<?php
require_once __DIR__ . '/../config/conexion.php';

class ModeloAdmin {

    // 1. Resumen
    public static function obtenerResumen() {
        global $pdo;
        try {
            $stmt = $pdo->prepare("CALL ObtenerResumenAdmin()");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return null; }
    }

    // 2. Listar Usuarios
    public static function listarUsuarios() {
        global $pdo;
        try {
            $stmt = $pdo->prepare("CALL ListarUsuariosAdmin()");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    // 3. Listar Equipos (Resumen corto para tabla de inicio)
    public static function listarEquipos() {
        global $pdo;
        try {
            $stmt = $pdo->prepare("CALL ListarEquiposAdmin()");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    // 4. Listar Equipos Detallado (PARA BASE DE DATOS EQUIPOS)
    // AHORA USA EL PROCEDIMIENTO ALMACENADO QUE CREAMOS
    public static function listarEquiposDetallado() {
        global $pdo;
        try {
            $stmt = $pdo->prepare("CALL ObtenerTodosLosEquiposDetallado()");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    // --- UTILERÍAS ---
    public static function obtenerEscuelaUsuario($id) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT codEscuela_EscuelaProcedencia FROM Entrenador WHERE idAsistente_Asistente = ?");
            $stmt->execute([$id]);
            $res = $stmt->fetchColumn();
            if($res) return $res;
            
            $stmt = $pdo->prepare("SELECT codEscuela_EscuelaProcedencia FROM Juez WHERE idAsistente_Asistente = ?");
            $stmt->execute([$id]);
            return $stmt->fetchColumn();
        } catch(Exception $e) { return null; }
    }

    // --- GESTIÓN DE ROLES ---
    public static function asignarRolEntrenador($id, $cod) { 
        global $pdo; try { 
            $sql = "INSERT INTO Entrenador (idEntrenador, idAsistente_Asistente, codEscuela_EscuelaProcedencia) 
                    VALUES (?, ?, ?) 
                    ON DUPLICATE KEY UPDATE codEscuela_EscuelaProcedencia = VALUES(codEscuela_EscuelaProcedencia)";
            $pdo->prepare($sql)->execute([$id, $id, $cod]); 
        } catch(Exception $e) {} 
    }

    public static function asignarRolJuez($id, $cod, $gr, $cat) { 
        global $pdo; try { 
            $sql = "INSERT INTO Juez (idJuez, idAsistente_Asistente, codEscuela_EscuelaProcedencia, gradoEstudios, idCategoria) 
                    VALUES (?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                        codEscuela_EscuelaProcedencia = VALUES(codEscuela_EscuelaProcedencia),
                        gradoEstudios = VALUES(gradoEstudios),
                        idCategoria = VALUES(idCategoria)"; 
            $pdo->prepare($sql)->execute([$id, $id, $cod, $gr, $cat]); 
        } catch(Exception $e) {} 
    }
    
    public static function quitarRolEntrenador($id) {
        global $pdo; try { $pdo->prepare("DELETE FROM Entrenador WHERE idAsistente_Asistente = ?")->execute([$id]); } catch(Exception $e) {}
    }

    public static function quitarRolJuez($id) {
        global $pdo; try { $pdo->prepare("DELETE FROM Juez WHERE idAsistente_Asistente = ?")->execute([$id]); } catch(Exception $e) {}
    }

    public static function verificarConflictoInteres($idAsistente, $idCategoriaJuez) {
        global $pdo; try {
            $sql = "SELECT COUNT(*) FROM Equipo WHERE idAsistente = ? AND idCategoria_Categoria = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$idAsistente, $idCategoriaJuez]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) { return false; }
    }

    // --- OTROS ---
    public static function crearEvento($nombre, $lugar, $fecha) {
        global $pdo; try { 
            $stmt = $pdo->prepare("CALL AltaEvento(?, ?, ?, @mensaje)");
            $stmt->execute([$nombre, $lugar, $fecha]);
            return $pdo->query("SELECT @mensaje")->fetchColumn();
        } catch (PDOException $e) { return $e->getMessage(); }
    }

    public static function crearEscuela($codigo, $nombre) {
        global $pdo; try {
            $stmt = $pdo->prepare("CALL AltaEscuelaProcedencia(?, ?, @mensaje)");
            $stmt->execute([$codigo, $nombre]);
            return $pdo->query("SELECT @mensaje")->fetchColumn();
        } catch (PDOException $e) { return $e->getMessage(); }
    }

    public static function eliminarEscuela($codigo) {
        global $pdo; try {
            $stmt = $pdo->prepare("DELETE FROM EscuelaProcedencia WHERE codEscuela = ?");
            $stmt->execute([$codigo]);
            return "Escuela eliminada correctamente.";
        } catch (PDOException $e) { 
            if($e->getCode() == '23000') return "No se puede borrar: Tiene datos vinculados.";
            return "Error: " . $e->getMessage(); 
        }
    }
    
    public static function obtenerJuecesValidos($cat) { 
        global $pdo; try{ 
            $s=$pdo->prepare("CALL ObtenerJuecesValidosParaCategoria(?)"); 
            $s->execute([$cat]); 
            return $s->fetchAll(PDO::FETCH_ASSOC); 
        }catch(Exception $e){return[];} 
    }

    public static function obtenerEquiposPorCategoria($cat) { 
        global $pdo; try{ 
            $s=$pdo->prepare("SELECT idEquipo FROM Equipo WHERE idCategoria_Categoria=?"); 
            $s->execute([$cat]); 
            return $s->fetchAll(PDO::FETCH_ASSOC); 
        }catch(Exception $e){return[];} 
    }

    public static function asignarJuezEquipo($juez, $equipo) { 
        global $pdo; try{ 
            $s=$pdo->prepare("CALL AsignarJuezEquipo(?, ?, @m)"); 
            $s->execute([$juez, $equipo]); 
            return $pdo->query("SELECT @m")->fetchColumn(); 
        }catch(Exception $e){return $e->getMessage();} 
    }
}
?>