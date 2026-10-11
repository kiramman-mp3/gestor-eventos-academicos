<?php
class Conexion
{
    private $host = "localhost";
    private $db = "UTA";
    private $user = "root";
    private $pass = "";
    private $charset = "utf8mb4";

    public function conectar()
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
            $conn = new PDO($dsn, $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("❌ Error de conexión: " . $e->getMessage());
        }
    }
}
?>