<?php
session_start();
require_once '../models/ModeloAdmin.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUsuario = $_POST['idUsuario'];
    $nuevoRol  = $_POST['nuevoRol'];
    $escuela   = $_POST['escuelaModal'];
    $categoria = $_POST['categoriaModal']; // Nuevo campo recibido

    // Si no seleccionó categoría (ej. para entrenador), ponemos null o manejamos error
    if($nuevoRol == 'juez' && empty($categoria)) {
        // Podrías forzar un error aquí si es obligatorio
    }

    if ($nuevoRol == 'juez') {
        ModeloAdmin::quitarRolEntrenador($idUsuario);
        // Pasamos la categoría al crear/editar el juez
        ModeloAdmin::asignarRolJuez($idUsuario, $escuela, 'Licenciatura', $categoria);
    }
    
    elseif ($nuevoRol == 'entrenador') {
        ModeloAdmin::quitarRolJuez($idUsuario);
        ModeloAdmin::asignarRolEntrenador($idUsuario, $escuela);
    }
    
    elseif ($nuevoRol == 'ambos') {
        ModeloAdmin::asignarRolEntrenador($idUsuario, $escuela);
        // Pasamos la categoría también aquí
        ModeloAdmin::asignarRolJuez($idUsuario, $escuela, 'Licenciatura', $categoria);
    }

    $mensaje = "Rol actualizado correctamente a: " . ucfirst($nuevoRol);
    header("Location: ../views/dashboards/adminDashboard.php?msg=" . urlencode($mensaje));
    exit;
}
?>