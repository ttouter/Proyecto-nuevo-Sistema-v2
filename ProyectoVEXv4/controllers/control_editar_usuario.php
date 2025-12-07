<?php
session_start();
require_once '../models/ModeloAdmin.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUsuario = $_POST['idUsuario'];
    $nuevoRol  = $_POST['nuevoRol'];
    $escuela   = isset($_POST['escuelaModal']) ? $_POST['escuelaModal'] : '';
    
    // NOTA: Ya no recibimos la categoría aquí. Se asignará después.
    $categoria = null;

    // LOGICA ACTUALIZADA: Buscar escuela actual
    if (empty($escuela)) {
        $escuelaActual = ModeloAdmin::obtenerEscuelaUsuario($idUsuario);
        if ($escuelaActual) {
            $escuela = $escuelaActual;
        } else {
            // Fallback en caso extremo
            header("Location: ../views/dashboards/adminDashboard.php?error=" . urlencode("Error Crítico: No se pudo determinar la escuela del usuario."));
            exit;
        }
    }

    // --- PROCESAMIENTO DE ROLES ---

    if ($nuevoRol == 'juez') {
        ModeloAdmin::quitarRolEntrenador($idUsuario);
        // Asignar Juez con categoría NULL (se define después en Asignación Jueces)
        ModeloAdmin::asignarRolJuez($idUsuario, $escuela, 'Licenciatura', null);
    }
    
    elseif ($nuevoRol == 'entrenador') {
        ModeloAdmin::quitarRolJuez($idUsuario);
        ModeloAdmin::asignarRolEntrenador($idUsuario, $escuela);
    }
    
    elseif ($nuevoRol == 'ambos') {
        // Asignar ambos roles. Juez inicia sin categoría asignada.
        ModeloAdmin::asignarRolEntrenador($idUsuario, $escuela);
        ModeloAdmin::asignarRolJuez($idUsuario, $escuela, 'Licenciatura', null);
    }

    $mensaje = "Rol actualizado correctamente a: " . ucfirst($nuevoRol);
    header("Location: ../views/dashboards/adminDashboard.php?msg=" . urlencode($mensaje));
    exit;
}
?>