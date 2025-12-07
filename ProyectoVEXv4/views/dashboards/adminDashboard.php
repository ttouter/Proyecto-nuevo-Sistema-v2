<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_activo'] != 'organizador') {
    header("Location: ../login/login_unificado.php");
    exit;
}

require_once '../../models/ModeloAdmin.php';
require_once '../../models/ModeloProcesos.php';

// Cargar Datos
$resumen = ModeloAdmin::obtenerResumen();
if (!$resumen) $resumen = ['total_equipos'=>0, 'eventos_activos'=>0, 'total_jueces'=>0, 'total_participantes'=>0];

$listaUsuarios = ModeloAdmin::listarUsuarios();
$listaEquipos  = ModeloAdmin::listarEquipos();
$listaEquiposCompleta = ModeloAdmin::listarEquiposDetallado();
$listaEventos  = ModeloProcesos::listarEventos();
$listaEscuelas = ModeloProcesos::listarEscuelas();
$listaCategorias = ModeloProcesos::listarCategorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - VEX Control</title>
    <link rel="icon" type="image/x-icon" href="../../assets/img/fav-robot.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root { --sidebar-bg: #2C2C54; --header-bg: #FDF5A3; --bg-body: #f4f4f9; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg-body); margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* SIDEBAR */
        .sidebar { width: 260px; background: var(--sidebar-bg); color: white; display: flex; flex-direction: column; padding: 20px; overflow-y: auto; }
        .brand { font-size: 1.5rem; font-weight: bold; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; justify-content: center; margin-top: 10px;}
        .menu-item { padding: 12px 15px; color: rgba(255,255,255,0.8); text-decoration: none; display: flex; align-items: center; gap: 15px; border-radius: 8px; transition: 0.3s; margin-bottom: 5px; cursor: pointer; font-size: 0.95rem; }
        .menu-item:hover, .menu-item.active { background: rgba(255,255,255,0.15); color: white; font-weight: bold; border-left: 4px solid var(--header-bg); }
        .logout { margin-top: auto; color: #ff6b6b; }
        
        /* MAIN */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .header-bar { background: var(--header-bg); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .work-area { flex: 1; padding: 30px; overflow-y: auto; }
        
        /* CARDS & FORMS */
        .content-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); margin-bottom: 30px; }
        .card-title { font-size: 1.1rem; color: var(--sidebar-bg); border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; font-weight: bold; }
        
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; margin-bottom:10px; box-sizing: border-box;}
        .btn-action { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-danger { background: #ff6b6b; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 0.8rem; }
        .btn-warning { background: #f1c40f; color: #333; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 0.8rem; font-weight: bold; }

        /* TABLAS */
        .vex-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .vex-table th { background: var(--sidebar-bg); color: white; padding: 12px; text-align: left; }
        .vex-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .tag { padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; }
        
        /* TAGS DE ESTADO */
        .tag-green { background: #d4edda; color: #155724; }
        .tag-blue  { background: #cce5ff; color: #004085; }
        .tag-red   { background: #f8d7da; color: #721c24; }
        .tag-purple { background: #e0ccff; color: #4a0080; }
        .tag-gray  { background: #e2e3e5; color: #383d41; }
        .text-muted { color: #aaa; font-style: italic; }

        /* SECCIONES */
        .section-view { display: none; }
        .section-view.active { display: block; animation: fadeIn 0.3s; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        /* TEAM CARDS */
        .filter-bar { background: white; padding: 15px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .teams-grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .team-card-interactive {
            background: white; border-radius: 15px; padding: 20px; position: relative; border: 1px solid #eee;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03); transition: all 0.3s; border-top: 5px solid #2C2C54;
        }
        .team-card-interactive:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.1); border-top-color: #FDF5A3; }
        .card-team-name { font-size: 1.2rem; font-weight: 800; color: #333; margin-bottom: 5px; }
        .card-school { font-size: 0.85rem; color: #666; display: flex; align-items: center; gap: 5px; margin-bottom: 15px; }
        .card-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; background: #eee; color: #555; margin-right: 5px; }
        .badge-cat { background: #e3f2fd; color: #1565c0; }
        .card-stats { display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #eee; font-size: 0.9rem; color: #555; }

        /* MODAL */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 400px; max-width: 90%; }
        .close-modal { float: right; cursor: pointer; font-size: 1.2rem; }

        /* ALERTAS FLOTANTES MEJORADAS */
        .alert-float { 
            position: fixed; top: 20px; right: 20px; 
            padding: 15px 25px; border-radius: 8px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2); 
            z-index: 2000; display: none; font-weight: bold;
            animation: slideInRight 0.5s ease-out;
        }
        
        .alert-success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .alert-error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* --- NUEVOS ESTILOS PARA GESTIÓN DE JUECES (LISTAS DOBLES) --- */
        .jueces-container {
            display: flex;
            gap: 30px;
            margin-top: 20px;
            height: 450px; /* Altura fija para scroll */
        }

        .jueces-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        }

        .col-header {
            padding: 15px;
            color: white;
            text-align: center;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .col-header.available { background: #7f8c8d; } /* Gris Profesional */
        .col-header.assigned { background: #2C2C54; } /* Azul VEX */

        .jueces-list {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .juez-card {
            background: white;
            padding: 15px;
            margin-bottom: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
            border: 1px solid #eee;
        }

        .juez-card:hover { transform: translateX(3px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .card-disp { border-left: 4px solid #27ae60; }
        .card-asig { border-left: 4px solid #c0392b; }

        .juez-info strong { display: block; color: #333; font-size: 0.95rem; }
        .juez-info small { color: #777; font-size: 0.85rem; display: flex; align-items: center; gap: 5px; margin-top: 3px; }

        .btn-move {
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .btn-add { background: #27ae60; }
        .btn-add:hover { background: #219150; transform: scale(1.1); }
        .btn-remove { background: #c0392b; }
        .btn-remove:hover { background: #a93226; transform: scale(1.1); }
        
        .loading, .empty-msg { text-align: center; padding: 30px; color: #999; font-style: italic; }
    </style>
</head>
<body>

    <!-- CAJAS DE ALERTA -->
    <div id="alertBoxSuccess" class="alert-float alert-success">
        <i class="fas fa-check-circle"></i> <span id="alertTextSuccess"></span>
    </div>
    
    <div id="alertBoxError" class="alert-float alert-error">
        <i class="fas fa-exclamation-triangle"></i> <span id="alertTextError"></span>
    </div>

    <aside class="sidebar">
        <div class="brand"><i class="fas fa-cogs"></i> &nbsp; VEX Control</div>
        <div class="menu-item active" onclick="showSection('resumen', this)"><i class="fas fa-home"></i> Resumen</div>
        <div class="menu-item" onclick="showSection('all-teams', this)"><i class="fas fa-list-alt"></i> Base de Datos Equipos</div>
        <div class="menu-item" onclick="showSection('eventos', this)"><i class="fas fa-calendar-alt"></i> Gestión de Eventos</div>
        <div class="menu-item" onclick="showSection('escuelas', this)"><i class="fas fa-university"></i> Escuelas</div>
        <div class="menu-item" onclick="showSection('asignacion', this)"><i class="fas fa-gavel"></i> Asignación Jueces</div>
        <div class="menu-item" onclick="showSection('usuarios', this)"><i class="fas fa-users-cog"></i> Monitor Usuarios</div>
        <a href="../login/terminarSesion_asistente.php" class="menu-item logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </aside>

    <div class="main-content">
        <header class="header-bar">
            <div style="font-weight:bold; color:#2C2C54; font-size:1.2rem;">Panel de Administración</div>
            <div>Hola, <strong>Admin</strong></div>
        </header>

        <div class="work-area">

            <!-- RESUMEN (Restaurado) -->
            <div id="resumen" class="section-view active">
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:30px;">
                    <div class="content-card" style="text-align:center;"><h3><?php echo $resumen['total_equipos']; ?></h3><p>Equipos</p></div>
                    <div class="content-card" style="text-align:center;"><h3><?php echo $resumen['eventos_activos']; ?></h3><p>Eventos Activos</p></div>
                    <div class="content-card" style="text-align:center;"><h3><?php echo $resumen['total_jueces']; ?></h3><p>Jueces</p></div>
                    <div class="content-card" style="text-align:center;"><h3><?php echo $resumen['total_participantes']; ?></h3><p>Participantes</p></div>
                </div>
                
                <div class="content-card">
                    <div class="card-title">Últimos Equipos Registrados</div>
                    <table class="vex-table">
                        <thead><tr><th>Nombre</th><th>Categoría</th><th>Escuela</th><th>Estado</th></tr></thead>
                        <tbody>
                            <?php if (!empty($listaEquipos)): ?>
                                <?php foreach ($listaEquipos as $eq): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($eq['nombreEquipo']); ?></td>
                                    <td><?php echo htmlspecialchars($eq['categoria']); ?></td>
                                    <td><?php echo htmlspecialchars($eq['nombreEscuela']); ?></td>
                                    <td><span class="tag tag-green"><?php echo $eq['estado']; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" style="text-align:center; padding:15px;">No hay equipos registrados aún.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- BASE DE DATOS EQUIPOS (Restaurado) -->
            <div id="all-teams" class="section-view">
                <div class="filter-bar">
                    <i class="fas fa-filter" style="color:var(--sidebar-bg);"></i>
                    <span style="font-weight:bold;">Filtrar por Escuela:</span>
                    <select id="schoolFilter" class="form-control" style="width: auto; margin:0;" onchange="filtrarEquipos()">
                        <option value="all">-- Mostrar Todas --</option>
                        <?php foreach($listaEscuelas as $esc): ?>
                            <option value="<?php echo $esc['codEscuela']; ?>">
                                <?php echo htmlspecialchars($esc['nombreEscuela']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span id="contadorEquipos" style="margin-left:auto; font-size:0.9rem; color:#777;">
                        Mostrando <?php echo count($listaEquiposCompleta); ?> equipos
                    </span>
                </div>

                <div class="teams-grid-container" id="gridEquipos">
                    <?php if (!empty($listaEquiposCompleta)): ?>
                        <?php foreach ($listaEquiposCompleta as $eq): ?>
                            <div class="team-card-interactive" data-escuela="<?php echo $eq['codEscuela']; ?>">
                                <div class="card-team-name"><?php echo htmlspecialchars($eq['nombreEquipo']); ?></div>
                                <div class="card-school"><i class="fas fa-university"></i> <?php echo htmlspecialchars($eq['nombreEscuela']); ?></div>
                                <div style="margin-bottom:10px;">
                                    <span class="card-badge badge-cat"><?php echo htmlspecialchars($eq['categoria']); ?></span>
                                    <span class="card-badge" style="background:<?php echo ($eq['estado']=='Activo')?'#d4edda':'#eee';?>">
                                        <?php echo $eq['estado']; ?>
                                    </span>
                                </div>
                                <div style="font-size:0.85rem; color:#888; margin-bottom:5px;">
                                    <i class="fas fa-user-tie"></i> Coach: <?php echo htmlspecialchars($eq['nombre_entrenador'] ?: 'Sin Asignar'); ?>
                                </div>
                                <div class="card-stats">
                                    <span><i class="fas fa-users"></i> <strong><?php echo $eq['total_integrantes']; ?></strong> Integrantes</span>
                                    <span><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($eq['nombre_Evento']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="grid-column: 1/-1; text-align:center; padding:40px; color:#999;">
                            <i class="fas fa-search" style="font-size:2rem; margin-bottom:10px;"></i>
                            <p>No se encontraron equipos registrados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- GESTIÓN DE EVENTOS (Restaurado) -->
            <div id="eventos" class="section-view">
                <div class="content-card">
                    <div class="card-title">Crear Nuevo Evento</div>
                    <form action="../../controllers/control_evento.php" method="POST">
                        <input type="text" name="nombreEvento" class="form-control" placeholder="Nombre del Evento" required>
                        <input type="text" name="lugarEvento" class="form-control" placeholder="Lugar / Sede" required>
                        <input type="date" name="fechaEvento" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                        <button class="btn-action">Guardar Evento</button>
                    </form>
                </div>
                <div class="content-card">
                    <div class="card-title">Eventos Activos</div>
                    <table class="vex-table">
                        <thead><tr><th>Evento</th><th>Lugar</th><th>Fecha</th><th>Estado</th></tr></thead>
                        <tbody>
                            <?php foreach ($listaEventos as $ev): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ev['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($ev['lugar']); ?></td>
                                    <td><?php echo htmlspecialchars($ev['fecha']); ?></td>
                                    <td><?php echo ($ev['fecha']>=date('Y-m-d'))?'<span class="tag tag-green">Activo</span>':'<span class="tag tag-gray">Finalizado</span>'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ESCUELAS (Restaurado) -->
            <div id="escuelas" class="section-view">
                <div class="content-card">
                    <div class="card-title">Registrar Institución</div>
                    <form action="../../controllers/control_escuela.php" method="POST">
                        <input type="text" name="codEscuela" class="form-control" placeholder="Siglas / Código (Ej. UNAM)" required>
                        <input type="text" name="nombreEscuela" class="form-control" placeholder="Nombre Completo" required>
                        <button class="btn-action">Guardar Escuela</button>
                    </form>
                </div>
                <div class="content-card">
                    <div class="card-title">Instituciones Registradas</div>
                    <table class="vex-table">
                        <thead><tr><th>Código</th><th>Nombre</th><th>Acciones</th></tr></thead>
                        <tbody>
                            <?php foreach ($listaEscuelas as $esc): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($esc['codEscuela']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($esc['nombreEscuela']); ?></td>
                                    <td>
                                        <a href="../../controllers/control_eliminar_escuela.php?id=<?php echo $esc['codEscuela']; ?>" 
                                           class="btn-danger" onclick="return confirm('¿Eliminar esta escuela?');"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ASIGNACIÓN JUECES (ACTUALIZADO CON LA NUEVA LÓGICA DE LISTAS) -->
            <div id="asignacion" class="section-view">
                <div class="content-card">
                    <div class="card-title"><i class="fas fa-gavel"></i> Gestión de Jueces por Categoría</div>
                    <p class="text-muted" style="margin-bottom: 25px;">Selecciona una categoría para administrar su panel de jueces. Podrás mover jueces disponibles a la categoría seleccionada y viceversa.</p>
                    
                    <!-- 1. SELECTOR DE CATEGORÍA -->
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
                        <label style="font-weight:bold; display:block; margin-bottom:8px;">Seleccionar Categoría:</label>
                        <select id="selectCategoriaJuez" class="form-control" style="max-width: 400px;">
                            <option value="">-- Selecciona una Categoría --</option>
                            <?php foreach($listaCategorias as $cat): ?>
                                <option value="<?php echo $cat['idCategoria']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 2. PANELES DE GESTIÓN (Oculto hasta seleccionar) -->
                    <div id="panelGestionJueces" class="jueces-container" style="display: none;">
                        
                        <!-- COLUMNA IZQUIERDA: DISPONIBLES -->
                        <div class="jueces-col">
                            <div class="col-header available">
                                <i class="fas fa-users"></i> Jueces Disponibles (Sin Asignar)
                            </div>
                            <div id="listaDisponibles" class="jueces-list">
                                <!-- Se llena con JS -->
                            </div>
                        </div>

                        <!-- COLUMNA DERECHA: ASIGNADOS -->
                        <div class="jueces-col">
                            <div class="col-header assigned">
                                <i class="fas fa-gavel"></i> Jueces en: <span id="labelCategoriaSeleccionada" style="margin-left:5px; text-decoration: underline;">...</span>
                            </div>
                            <div id="listaAsignados" class="jueces-list">
                                <!-- Se llena con JS -->
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- USUARIOS (Restaurado) -->
            <div id="usuarios" class="section-view">
                <div class="content-card">
                    <div class="card-title">Gestión de Usuarios</div>
                    <table class="vex-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <!-- Columnas modificadas -->
                                <th>Entrenador</th>
                                <th>Juez</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($listaUsuarios as $u): ?>
                                <?php 
                                    $rol = $u['rol_detectado'];
                                    
                                    // Columna Entrenador
                                    $colEntrenador = '<span class="text-muted">No participa</span>';
                                    if ($rol == 'Entrenador' || $rol == 'Ambos') {
                                        $cat = !empty($u['cat_entrenador']) ? $u['cat_entrenador'] : 'Activo (Sin equipos)';
                                        $colEntrenador = '<div style="color:#155724;"><i class="fas fa-user-tie"></i> ' . $cat . '</div>';
                                    }

                                    // Columna Juez
                                    $colJuez = '<span class="text-muted">No participa</span>';
                                    if ($rol == 'Juez' || $rol == 'Ambos') {
                                        $cat = !empty($u['cat_juez']) ? $u['cat_juez'] : 'Asignado';
                                        $colJuez = '<div style="color:#721c24;"><i class="fas fa-gavel"></i> ' . $cat . '</div>';
                                    }
                                ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo $colEntrenador; ?></td>
                                <td><?php echo $colJuez; ?></td>
                                <td>
                                    <button class="btn-warning" onclick="abrirModalEditar(<?php echo $u['idAsistente']; ?>, '<?php echo $u['nombre']; ?>')"><i class="fas fa-edit"></i> Editar Rol</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR USUARIO (Restaurado) -->
    <div id="modalEditarUsuario" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="cerrarModal()">&times;</span>
            <h3>Editar Rol</h3>
            <p>Usuario: <strong id="nombreUsuarioModal">...</strong></p>
            <form action="../../controllers/control_editar_usuario.php" method="POST">
                <input type="hidden" name="idUsuario" id="idUsuarioModal">
                
                <label>Nuevo Rol:</label>
                <select name="nuevoRol" id="nuevoRolSelect" class="form-control" onchange="toggleCategoriaSelect()">
                    <option value="entrenador">Entrenador</option>
                    <option value="juez">Juez</option>
                    <option value="ambos">Ambos</option>
                </select>

                <!-- Select de Categoría (Solo visible para Juez/Ambos) -->
                <div id="divCategoriaModal" style="display:none;">
                    <label>Categoría para Juez:</label>
                    <select name="categoriaModal" class="form-control">
                        <option value="">-- Selecciona Categoría --</option>
                        <?php foreach ($listaCategorias as $cat): ?>
                            <option value="<?php echo $cat['idCategoria']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <label>Escuela (Procedencia):</label>
                <select name="escuelaModal" class="form-control">
                     <?php foreach ($listaEscuelas as $esc): ?>
                        <option value="<?php echo $esc['codEscuela']; ?>"><?php echo htmlspecialchars($esc['nombreEscuela']); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn-action" style="width:100%; margin-top:10px;">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
        function showSection(id, el) {
            document.querySelectorAll('.section-view').forEach(d => d.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
            el.classList.add('active');
        }

        // --- SISTEMA DE NOTIFICACIONES MEJORADO ---
        const urlParams = new URLSearchParams(window.location.search);
        
        // 1. Mensaje de Éxito (Verde)
        const msg = urlParams.get('msg');
        if(msg) {
            const alertBox = document.getElementById('alertBoxSuccess');
            document.getElementById('alertTextSuccess').innerText = msg;
            alertBox.style.display = 'block';
            setTimeout(() => { alertBox.style.display = 'none'; }, 5000);
            
            // Limpiar URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // 2. Mensaje de Error (Rojo)
        const error = urlParams.get('error');
        if(error) {
            const alertBox = document.getElementById('alertBoxError');
            document.getElementById('alertTextError').innerText = error;
            alertBox.style.display = 'block';
            setTimeout(() => { alertBox.style.display = 'none'; }, 6000);

            // Limpiar URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        // Filtro y demás scripts...
        function filtrarEquipos() {
            const filtro = document.getElementById('schoolFilter').value;
            const tarjetas = document.querySelectorAll('.team-card-interactive');
            let visibles = 0;
            tarjetas.forEach(card => {
                const escuela = card.getAttribute('data-escuela');
                if (filtro === 'all' || escuela === filtro) {
                    card.style.display = 'block'; visibles++;
                } else {
                    card.style.display = 'none';
                }
            });
            document.getElementById('contadorEquipos').innerText = 'Mostrando ' + visibles + ' equipos';
        }
        
        function abrirModalEditar(id, nombre) {
            document.getElementById('idUsuarioModal').value=id;
            document.getElementById('nombreUsuarioModal').innerText=nombre;
            document.getElementById('modalEditarUsuario').style.display='flex';
            toggleCategoriaSelect(); // Verificar estado inicial
        }
        
        function cerrarModal() { document.getElementById('modalEditarUsuario').style.display='none'; }
        
        function toggleCategoriaSelect() {
            const rol = document.getElementById('nuevoRolSelect').value;
            const divCat = document.getElementById('divCategoriaModal');
            if(rol === 'juez' || rol === 'ambos') {
                divCat.style.display = 'block';
            } else {
                divCat.style.display = 'none';
            }
        }

        window.onclick = function(e) { if(e.target == document.getElementById('modalEditarUsuario')) cerrarModal(); }
    </script>
    
    <!-- SCRIPT DE LOS JUECES -->
    <script src="../../assets/js/admin_jueces.js"></script>
</body>
</html>