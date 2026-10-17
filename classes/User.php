<?php
class User
{
    public function __construct(private PDO $db) {}

    public function findByEmail(string $email): ?array
    {
        $s = $this->db->prepare('SELECT * FROM user WHERE email = ?');
        $s->execute([$email]);
        return $s->fetch() ?: null;
    }

    public function find(int $id): ?array
    {
        $s = $this->db->prepare('SELECT id_user, nom, prenom, email, created_at FROM user WHERE id_user = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public function create(string $nom, string $prenom, string $email, string $password): int
    {
        $s = $this->db->prepare('INSERT INTO user (nom, prenom, email, motdepasse) VALUES (?, ?, ?, ?)');
        $s->execute([$nom, $prenom, $email, password_hash($password, PASSWORD_DEFAULT)]);
        return (int)$this->db->lastInsertId();
    }
}
