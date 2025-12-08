<?php
require_once __DIR__ . '/../config/conexion.php';

class ModeloEntrenador {

    // Obtener toda la info para el dashboard en una sola carga
    public static function obtenerDatosDashboard($idAsistente) {
        global $pdo;
        $stmt = null; // Inicializar para que esté disponible en el finally
        try {
            // 1. Prepara y ejecuta la llamada al SP
            $stmt = $pdo->prepare("CALL ObtenerDashboardEntrenador(?)");
            $stmt->execute([$idAsistente]);
            
            // 2. Primer conjunto de resultados: Datos de Escuela
            $escuela = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->nextRowset(); // Avanzar al siguiente set de resultados
            
            // 3. Segundo conjunto de resultados: Lista de Equipos
            $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['escuela' => $escuela, 'equipos' => $equipos];
        } catch (PDOException $e) { 
            // Esto imprimirá el error real de MySQL en el log del servidor
            error_log("Error al obtener datos del dashboard del entrenador: " . $e->getMessage());
            return null; 
        } finally {
            // Cierre explícito del cursor (muy importante para SPs con múltiples resultados)
            if ($stmt) {
                // Si hay más resultados pendientes, los descarta para que la siguiente consulta funcione
                while ($stmt->nextRowset()) {;} 
                $stmt->closeCursor();
            }
        }
    }

    // Obtener integrantes de un equipo para verlos en detalle
    public static function obtenerIntegrantes($idEquipo) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("CALL ObtenerIntegrantesEquipo(?)");
            $stmt->execute([$idEquipo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    // Registrar Equipo (Automático con la escuela del profe)
    public static function registrarEquipo($nombre, $categoria, $codEscuela, $evento, $idAsistente) {
        global $pdo;
        try {
            // Reutilizamos el SP AltaEquipo que ya tenías
            $stmt = $pdo->prepare("CALL AltaEquipo(?, ?, ?, ?, ?, @mensaje, @idOut)");
            $stmt->execute([$nombre, $categoria, $codEscuela, $evento, $idAsistente]);
            $res = $pdo->query("SELECT @mensaje as msg")->fetch();
            return $res['msg'];
        } catch (PDOException $e) { return $e->getMessage(); }
    }
}