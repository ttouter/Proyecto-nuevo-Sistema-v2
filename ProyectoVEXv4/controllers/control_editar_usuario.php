<?php
session_start();
require_once '../models/ModeloAdmin.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUsuario = $_POST['idUsuario'];
    $nuevoRol  = $_POST['nuevoRol'];
    $escuela   = $_POST['escuelaModal'];
    $categoria = $_POST['categoriaModal']; // Recibimos la categoría seleccionada

    // Validación básica: Si es Juez o Ambos, la categoría es obligatoria
    if (($nuevoRol == 'juez' || $nuevoRol == 'ambos') && empty($categoria)) {
        header("Location: ../views/dashboards/adminDashboard.php?error=" . urlencode("Error: Debes seleccionar una categoría para asignar el rol de Juez."));
        exit;
    }

    // --- NUEVA RESTRICCIÓN: CONFLICTO DE INTERÉS ---
    // Si se intenta asignar rol de Juez (o Ambos), verificar que no tenga equipos en esa misma categoría
    if ($nuevoRol == 'juez' || $nuevoRol == 'ambos') {
        if (ModeloAdmin::verificarConflictoInteres($idUsuario, $categoria)) {
            $msg = "Error: Conflicto de interés. Este usuario ya es Entrenador de un equipo en esa categoría, por lo tanto no puede ser Juez en la misma.";
            header("Location: ../views/dashboards/adminDashboard.php?error=" . urlencode($msg));
            exit;
        }
    }

    // --- PROCESAMIENTO DE ROLES ---

    if ($nuevoRol == 'juez') {
        ModeloAdmin::quitarRolEntrenador($idUsuario);
        // Asignar Juez con la categoría seleccionada
        ModeloAdmin::asignarRolJuez($idUsuario, $escuela, 'Licenciatura', $categoria);
    }
    
    elseif ($nuevoRol == 'entrenador') {
        ModeloAdmin::quitarRolJuez($idUsuario);
        ModeloAdmin::asignarRolEntrenador($idUsuario, $escuela);
    }
    
    elseif ($nuevoRol == 'ambos') {
        // Asignar ambos roles
        ModeloAdmin::asignarRolEntrenador($idUsuario, $escuela);
        ModeloAdmin::asignarRolJuez($idUsuario, $escuela, 'Licenciatura', $categoria);
    }

    $mensaje = "Rol actualizado correctamente a: " . ucfirst($nuevoRol);
    header("Location: ../views/dashboards/adminDashboard.php?msg=" . urlencode($mensaje));
    exit;
}
?>