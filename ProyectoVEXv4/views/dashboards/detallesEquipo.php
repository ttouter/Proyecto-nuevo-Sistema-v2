<?php
session_start();
// 1. Verificación de Seguridad y Rol
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_activo'] != 'entrenador') {
    header("Location: ../login/login_unificado.php");
    exit;
}

// 2. Verificar que recibimos un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: asistenteDashboard.php");
    exit;
}

require_once '../../models/ModeloEntrenador.php';
require_once '../../models/ModeloProcesos.php';

$idEquipo = $_GET['id'];

// 3. Consultar Información
$infoEquipo = ModeloProcesos::obtenerInfoEquipo($idEquipo);
$integrantes = ModeloEntrenador::obtenerIntegrantes($idEquipo);

// Simulamos calificaciones
$calificaciones = [
    'diseno' => 'Pendiente', 
    'programacion' => 'Pendiente', 
    'construccion' => 'Pendiente'
];

if (!$infoEquipo) {
    echo "<script>alert('El equipo solicitado no existe.'); window.location.href='asistenteDashboard.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles del Equipo | VEX Robotics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Importamos estilos base pero sobreescribiremos el layout -->
    <link rel="stylesheet" href="../../assets/css/styles_asistenteDashboard.css">
    
    <style>
        /* --- LAYOUT OVERRIDES (Para cambiar de Sidebar a Topbar) --- */
        body { 
            display: block !important; /* Quitamos el flex del dashboard */
            height: auto !important; 
            overflow-y: auto !important; 
            background-color: #f4f7f6;
        }
        
        /* Ocultamos la sidebar original si se cargó por CSS */
        .sidebar { display: none !important; }

        /* --- NUEVA BARRA DE NAVEGACIÓN SUPERIOR --- */
        .navbar-top {
            background-color: #2C2C54;
            color: white;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #FDF5A3; /* Acento amarillo */
        }

        .nav-actions {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: rgba(255,255,255,0.8);
            font-weight: 500;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            padding: 8px 15px;
            border-radius: 6px;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        .btn-logout {
            background: rgba(231, 76, 60, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(231, 76, 60, 0.4);
        }
        
        .btn-logout:hover {
            background: #c0392b;
            color: white;
            border-color: #c0392b;
        }

        /* --- CONTENEDOR PRINCIPAL --- */
        .container { 
            max-width: 1100px; 
            margin: 40px auto; 
            padding: 0 20px; 
            animation: fadeIn 0.5s ease-out; 
        }

        /* Resto de estilos del contenido (Tarjetas, tablas, etc.) */
        .hero-card {
            background: white; border-radius: 16px; padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06); margin-bottom: 30px;
            position: relative; overflow: hidden;
            display: flex; align-items: center; justify-content: space-between;
        }
        
        .hero-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%;
            background: linear-gradient(to bottom, #2C2C54, #40407A);
        }

        .team-info h1 { font-size: 2.2rem; color: #2C2C54; margin: 0 0 8px 0; font-weight: 800; }
        .team-info .school { color: #666; font-size: 1.1rem; display: flex; align-items: center; gap: 8px; }
        
        .meta-badge { 
            display: inline-block; padding: 6px 14px; border-radius: 50px; 
            font-weight: bold; font-size: 0.85rem; margin-left: 10px;
        }
        .badge-cat { background: #e8eaf6; color: #3f51b5; }
        .badge-active { background: #e0f2f1; color: #00897b; }

        .content-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        @media (max-width: 900px) { .content-grid { grid-template-columns: 1fr; } }

        .section-box { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; }
        
        .member-card {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 15px; border-radius: 8px; border: 1px solid #f0f0f0; margin-bottom: 10px;
            transition: 0.2s;
        }
        .member-card:hover { border-color: #40407A; background: #fafafa; }
        
        .member-avatar { 
            width: 40px; height: 40px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; color: white; margin-right: 15px; font-size: 1rem;
        }

        .score-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #eee; font-size: 0.9rem; }
        .score-row:last-child { border: none; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <!-- NUEVA BARRA SUPERIOR -->
    <nav class="navbar-top">
        <div class="nav-brand">
            <i class="fas fa-robot fa-lg"></i>
            <span>VEX Coach</span>
        </div>
        
        <div class="nav-actions">
            <a href="asistenteDashboard.php" class="nav-link">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
            <div style="width: 1px; height: 24px; background: rgba(255,255,255,0.2); margin: 0 5px;"></div>
            <a href="../login/terminarSesion_asistente.php" class="nav-link btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </nav>

    <div class="container">
        
        <!-- HEADER DEL EQUIPO -->
        <div class="hero-card">
            <div class="team-info">
                <div style="display:flex; align-items:center; flex-wrap:wrap; gap:10px;">
                    <h1><?php echo htmlspecialchars($infoEquipo['nombreEquipo']); ?></h1>
                    <span class="meta-badge badge-active"><i class="fas fa-check-circle"></i> Activo</span>
                </div>
                <div class="school">
                    <i class="fas fa-university" style="color: #40407A;"></i>
                    <?php echo htmlspecialchars($infoEquipo['nombreEscuela']); ?>
                </div>
            </div>
            
            <div style="text-align:right;">
                <span style="display:block; font-size:0.8rem; color:#888; margin-bottom:5px;">ID DE EQUIPO</span>
                <span style="font-size:2rem; font-weight:800; color:#ddd;">#<?php echo $idEquipo; ?></span>
            </div>
        </div>

        <div class="content-grid">
            
            <!-- LISTA DE INTEGRANTES -->
            <div class="section-box">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:10px; border-bottom:2px solid #f4f7f6;">
                    <h3 style="margin:0; color:#2C2C54;"><i class="fas fa-users" style="color:#3498db;"></i> Integrantes</h3>
                    <span class="meta-badge badge-cat"><?php echo htmlspecialchars($infoEquipo['categoria']); ?></span>
                </div>

                <?php if (empty($integrantes)): ?>
                    <div style="text-align:center; padding:30px; color:#999;">
                        <i class="fas fa-user-plus fa-2x" style="opacity:0.3; margin-bottom:10px;"></i>
                        <p>No hay integrantes registrados.</p>
                        <a href="asistenteDashboard.php" style="color:#3498db; text-decoration:none; font-weight:bold;">Ir a agregar</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($integrantes as $int): ?>
                        <div class="member-card">
                            <div style="display:flex; align-items:center;">
                                <div class="member-avatar" style="background: <?php echo ($int['sexo']=='Mujer'?'#e84393':'#0984e3'); ?>;">
                                    <?php echo strtoupper(substr($int['nombre'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight:700; color:#333;">
                                        <?php echo htmlspecialchars($int['nombre'] . ' ' . $int['apellidoPat']); ?>
                                    </div>
                                    <div style="font-size:0.8rem; color:#888;">
                                        <?php echo $int['edad']; ?> años • No. Control: <?php echo $int['numControl']; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="../../controllers/control_eliminar_participante.php?id=<?php echo $int['numControl']; ?>" 
                               onclick="return confirm('¿Eliminar a este integrante?');"
                               style="color:#ff7675; text-decoration:none; padding:8px; border-radius:50%; transition:0.2s;">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- PANEL LATERAL (RESUMEN) -->
            <div>
                <!-- Estado Evaluaciones -->
                <div class="section-box">
                    <h4 style="margin:0 0 15px 0; color:#2C2C54;"><i class="fas fa-clipboard-list"></i> Estado Evaluación</h4>
                    <div class="score-row">
                        <span>Diseño</span>
                        <strong style="color:#f39c12;"><?php echo $calificaciones['diseno']; ?></strong>
                    </div>
                    <div class="score-row">
                        <span>Programación</span>
                        <strong style="color:#f39c12;"><?php echo $calificaciones['programacion']; ?></strong>
                    </div>
                    <div class="score-row">
                        <span>Construcción</span>
                        <strong style="color:#f39c12;"><?php echo $calificaciones['construccion']; ?></strong>
                    </div>
                </div>

                <!-- Botón Acción -->
                <div class="section-box" style="background:#2C2C54; color:white; text-align:center;">
                    <i class="fas fa-tools fa-2x" style="color:#FDF5A3; margin-bottom:15px;"></i>
                    <p style="font-size:0.9rem; opacity:0.8; margin-bottom:20px;">¿Necesitas actualizar datos del equipo?</p>
                    <button onclick="alert('Próximamente')" style="background:#FDF5A3; border:none; color:#2C2C54; font-weight:bold; padding:10px 20px; border-radius:6px; cursor:pointer; width:100%;">
                        Editar Equipo
                    </button>
                </div>
            </div>

        </div>
    </div>

</body>
</html>