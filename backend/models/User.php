<?php

require_once __DIR__ . '/../config/Database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function usuarioExiste(string $correo): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM usuarios WHERE correo = :correo'
        );

        $stmt->execute([
            'correo' => $correo
        ]);

        return $stmt->fetchColumn() !== false;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO usuarios
                    (usuario, correo, contrasena, cargo)
                VALUES
                    (:usuario, :correo, :contrasena, :cargo)';

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'usuario'    => trim($data['usuario']),
            'correo'     => trim($data['correo']),
            'contrasena' => $data['contrasena'],
            'cargo'      => $data['cargo'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByCorreo(string $correo): ?array
    {
        $sql = 'SELECT * FROM usuarios WHERE correo = :correo';

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'correo' => $correo
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario ?: null;
    }
}