<?php
session_start();
require_once '../models/ModeloJueces.php';

// Validar que sea una petición POST y usuario autorizado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['rol_activo']) && $_SESSION['rol_activo'] == 'organizador') {
    
    // Leer el JSON enviado por JavaScript
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idJuez = $input['idJuez'] ?? 0;
    $idCat  = $input['idCategoria'] ?? 0;
    
    $resultado = false;

    if ($accion === 'asignar' && $idJuez && $idCat) {
        $resultado = ModeloJueces::asignarCategoria($idJuez, $idCat);
    } 
    elseif ($accion === 'liberar' && $idJuez) {
        $resultado = ModeloJueces::liberarCategoria($idJuez);
    }

    echo json_encode(['success' => $resultado]);

} else {
    http_response_code(403);
    echo json_encode(['success' => false, 'msg' => 'Acceso denegado']);
}
?>