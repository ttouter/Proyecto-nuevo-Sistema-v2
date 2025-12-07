<?php
require_once __DIR__ . '/../config/conexion.php';

class ModeloJueces {

    // Obtener jueces que NO tienen categoría asignada (Disponibles)
    public static function obtenerDisponibles() {
        global $pdo;
        try {
            $sql = "SELECT j.idJuez, a.nombre, a.apellidoPat, ep.nombreEscuela 
                    FROM Juez j 
                    JOIN Asistente a ON j.idAsistente_Asistente = a.idAsistente 
                    LEFT JOIN EscuelaProcedencia ep ON j.codEscuela_EscuelaProcedencia = ep.codEscuela
                    WHERE j.idCategoria IS NULL";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    // Obtener jueces asignados a una categoría específica
    public static function obtenerPorCategoria($idCategoria) {
        global $pdo;
        try {
            $sql = "SELECT j.idJuez, a.nombre, a.apellidoPat, ep.nombreEscuela 
                    FROM Juez j 
                    JOIN Asistente a ON j.idAsistente_Asistente = a.idAsistente 
                    LEFT JOIN EscuelaProcedencia ep ON j.codEscuela_EscuelaProcedencia = ep.codEscuela
                    WHERE j.idCategoria = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idCategoria]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    // Asignar una categoría a un juez (Mover a la derecha)
    public static function asignarCategoria($idJuez, $idCategoria) {
        global $pdo;
        try {
            $sql = "UPDATE Juez SET idCategoria = ? WHERE idJuez = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$idCategoria, $idJuez]);
        } catch (PDOException $e) { return false; }
    }

    // Quitar categoría a un juez (Mover a la izquierda / Disponibles)
    public static function liberarCategoria($idJuez) {
        global $pdo;
        try {
            $sql = "UPDATE Juez SET idCategoria = NULL WHERE idJuez = ?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([$idJuez]);
        } catch (PDOException $e) { return false; }
    }
}
?>