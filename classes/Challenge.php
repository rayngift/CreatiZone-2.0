<?php
class Challenge
{
    public function __construct(private PDO $db) {}

    private function query(string $where, array $params, string $order): array
    {
        $sql = "SELECT c.*, u.nom, u.prenom,
                  (SELECT COUNT(*) FROM submissions s WHERE s.id_challenge = c.id_challenge) AS nb_participants,
                  (SELECT ROUND(AVG(v.note), 1) FROM votes v
                     JOIN submissions s ON s.id_submission = v.id_submission
                    WHERE s.id_challenge = c.id_challenge) AS note_moyenne
                FROM challenge c JOIN user u ON u.id_user = c.id_user
                $where ORDER BY $order";
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    public function search(string $q = '', string $cat = '', string $sort = 'recent'): array
    {
        $w = []; $p = [];
        if ($q !== '')   { $w[] = '(c.titre LIKE ? OR c.description LIKE ?)'; $p[] = "%$q%"; $p[] = "%$q%"; }
        if ($cat !== '') { $w[] = 'c.categorie = ?'; $p[] = $cat; }
        $order = [
            'recent'   => 'c.date_creation DESC',
            'deadline' => 'c.deadline ASC',
            'popular'  => 'nb_participants DESC',
            'rated'    => 'note_moyenne DESC',
        ][$sort] ?? 'c.date_creation DESC';
        return $this->query($w ? 'WHERE ' . implode(' AND ', $w) : '', $p, $order);
    }

    public function find(int $id): ?array
    {
        return $this->query('WHERE c.id_challenge = ?', [$id], 'c.id_challenge')[0] ?? null;
    }

    public function byUser(int $uid): array
    {
        return $this->query('WHERE c.id_user = ?', [$uid], 'c.date_creation DESC');
    }

    public function create(int $uid, string $titre, string $desc, string $cat, string $deadline, ?string $image): int
    {
        $s = $this->db->prepare('INSERT INTO challenge (id_user, titre, description, categorie, deadline, image) VALUES (?,?,?,?,?,?)');
        $s->execute([$uid, $titre, $desc, $cat, $deadline, $image]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $uid, string $titre, string $desc, string $cat, string $deadline, ?string $image): bool
    {
        $s = $this->db->prepare('UPDATE challenge SET titre=?, description=?, categorie=?, deadline=?, image=? WHERE id_challenge=? AND id_user=?');
        $s->execute([$titre, $desc, $cat, $deadline, $image, $id, $uid]);
        return $s->rowCount() > 0;
    }

    public function delete(int $id, int $uid): bool
    {
        $s = $this->db->prepare('DELETE FROM challenge WHERE id_challenge=? AND id_user=?');
        $s->execute([$id, $uid]);
        return $s->rowCount() > 0;
    }
}
