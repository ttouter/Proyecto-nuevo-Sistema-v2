<?php
session_start();
require_once '../models/ModeloAdmin.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombreEvento']);
    $lugar  = trim($_POST['lugarEvento']);
    $fecha  = $_POST['fechaEvento'];

    // Validacion previa simple
    if(empty($nombre) || empty($lugar) || empty($fecha)){
        header("Location: ../views/dashboards/adminDashboard.php?error=" . urlencode("Todos los campos son obligatorios"));
        exit;
    }

    // Intentamos crear el evento
    // El modelo llamará al SP AltaEvento que acabamos de mejorar
    $resultado = ModeloAdmin::crearEvento($nombre, $lugar, $fecha);

    // ANALISIS DE LA RESPUESTA DE LA BD
    // Si la respuesta empieza con "Error:", regresamos una alerta de error (Roja)
    if (strpos($resultado, 'Error:') !== false) {
        // Quitamos el prefijo "Error: " para que el mensaje sea más limpio si quieres, o lo dejas completo
        header("Location: ../views/dashboards/adminDashboard.php?error=" . urlencode($resultado));
    } else {
        // Si no es error, es éxito (Verde)
        header("Location: ../views/dashboards/adminDashboard.php?msg=" . urlencode($resultado));
    }
    exit;
} else {
    header("Location: ../views/dashboards/adminDashboard.php");
    exit;
}
?>