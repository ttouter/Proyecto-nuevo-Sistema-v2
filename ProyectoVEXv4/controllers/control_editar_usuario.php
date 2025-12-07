<?php
session_start();
require_once '../models/ModeloAdmin.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUsuario = $_POST['idUsuario'];
    $nuevoRol  = $_POST['nuevoRol'];
    $escuela   = $_POST['escuelaModal'];

    // Lógica Correcta: Limpiar roles antes de asignar
    
    if ($nuevoRol == 'juez') {
        // 1. Quitar rol contrario
        ModeloAdmin::quitarRolEntrenador($idUsuario);
        // 2. Asignar nuevo rol
        ModeloAdmin::asignarRolJuez($idUsuario, $escuela, 'Licenciatura');
    }
    
    elseif ($nuevoRol == 'entrenador') {
        // 1. Quitar rol contrario
        ModeloAdmin::quitarRolJuez($idUsuario);
        // 2. Asignar nuevo rol
        ModeloAdmin::asignarRolEntrenador($idUsuario, $escuela);
    }
    
    elseif ($nuevoRol == 'ambos') {
        // Asegurar que tenga ambos registros
        ModeloAdmin::asignarRolEntrenador($idUsuario, $escuela);
        ModeloAdmin::asignarRolJuez($idUsuario, $escuela, 'Licenciatura');
    }

    $mensaje = "Rol actualizado correctamente a: " . ucfirst($nuevoRol);
    header("Location: ../views/dashboards/adminDashboard.php?msg=" . urlencode($mensaje));
    exit;
}
?>