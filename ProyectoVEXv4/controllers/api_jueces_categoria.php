<?php
session_start();
require_once '../models/ModeloJueces.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol_activo'] != 'organizador') {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if (isset($_GET['cat'])) {
    $idCategoria = $_GET['cat'];
    
    // Obtener las dos listas
    $disponibles = ModeloJueces::obtenerDisponibles();
    $asignados = ModeloJueces::obtenerPorCategoria($idCategoria);
    
    echo json_encode([
        'disponibles' => $disponibles,
        'asignados' => $asignados
    ]);
} else {
    echo json_encode(['disponibles' => [], 'asignados' => []]);
}
?>