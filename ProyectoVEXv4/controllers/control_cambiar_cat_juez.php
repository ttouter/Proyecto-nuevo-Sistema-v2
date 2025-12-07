<?php
session_start();
require_once '../models/ModeloJueces.php';
require_once '../models/ModeloAdmin.php'; // Necesario para verificarConflictoInteres

// Validar que sea una petición POST y usuario autorizado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['rol_activo']) && $_SESSION['rol_activo'] == 'organizador') {
    
    // Leer el JSON enviado por JavaScript
    $input = json_decode(file_get_contents('php://input'), true);
    
    $accion = $input['accion'] ?? '';
    $idJuez = $input['idJuez'] ?? 0;
    $idCat  = $input['idCategoria'] ?? 0;
    
    $resultado = false;
    $msg = "";

    if ($accion === 'asignar' && $idJuez && $idCat) {
        
        // 1. VALIDACIÓN DE LÍMITE (MÁXIMO 3 JUECES)
        $juecesActuales = ModeloJueces::obtenerPorCategoria($idCat);
        if (count($juecesActuales) >= 3) {
            echo json_encode(['success' => false, 'msg' => 'Error: Límite alcanzado. Solo se permiten 3 jueces por categoría.']);
            exit;
        }

        // 2. VALIDACIÓN DE CONFLICTO DE INTERÉS
        // Verificar si este usuario (que es Juez) también es Entrenador en esta misma categoría
        if (ModeloAdmin::verificarConflictoInteres($idJuez, $idCat)) {
            echo json_encode(['success' => false, 'msg' => 'Error: Conflicto de interés. Este usuario ya es Entrenador de un equipo en esta categoría, por lo tanto no puede ser Juez en la misma.']);
            exit;
        }

        // Si no hay conflicto y hay espacio, procedemos a asignar
        $resultado = ModeloJueces::asignarCategoria($idJuez, $idCat);
    } 
    elseif ($accion === 'liberar' && $idJuez) {
        $resultado = ModeloJueces::liberarCategoria($idJuez);
    }

    if ($resultado) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Error al actualizar la categoría en la base de datos.']);
    }

} else {
    http_response_code(403);
    echo json_encode(['success' => false, 'msg' => 'Acceso denegado']);
}
?>