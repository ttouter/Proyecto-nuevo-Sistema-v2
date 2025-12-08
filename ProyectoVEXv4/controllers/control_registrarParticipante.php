<?php
session_start();
require_once '../models/ModeloProcesos.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['usuario_id'])) {
    
    $idEquipo   = $_POST['idEquipo'];
    $numControl = $_POST['numControl'];
    $nombre     = trim($_POST['nombre']);
    $apPat      = trim($_POST['apPat']);
    $apMat      = trim($_POST['apMat']);
    $edad       = $_POST['edad'];
    $sexo       = $_POST['sexo'];

    // Validaciones básicas de campos vacíos
    if(empty($nombre) || empty($idEquipo) || empty($numControl)){
         header("Location: ../views/dashboards/asistenteDashboard.php?error=" . urlencode("Por favor complete todos los campos requeridos."));
         exit;
    }

    // Llamar al modelo que ejecuta el Procedimiento Almacenado modificado
    $resultado = ModeloProcesos::registrarParticipante($numControl, $nombre, $apPat, $apMat, $edad, $sexo, $idEquipo);

    // Detección de Errores desde la Base de Datos
    // Si el SP devuelve una cadena que empieza con "Error:", lo tratamos como error (Rojo)
    if (strpos($resultado, 'Error:') !== false) {
        header("Location: ../views/dashboards/asistenteDashboard.php?error=" . urlencode($resultado));
    } else {
        // Si no, es éxito (Verde)
        header("Location: ../views/dashboards/asistenteDashboard.php?msg=" . urlencode($resultado));
    }
    exit;

} else {
    header("Location: ../views/login/login_unificado.php");
    exit;
}
?>