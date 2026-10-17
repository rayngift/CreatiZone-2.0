<?php
class Submission
{
    public function __construct(private PDO $db) {}

    public function find(int $id): ?array
    {
        $s = $this->db->prepare('SELECT * FROM submissions WHERE id_submission = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public function forChallenge(int $cid, string $sort, ?int $uid): array
    {
        $order = $sort === 'votes' ? 'note_moyenne DESC, nb_votes DESC' : 's.created_at DESC';
        $s = $this->db->prepare("SELECT s.*, u.nom, u.prenom,
              (SELECT ROUND(AVG(note), 1) FROM votes WHERE id_submission = s.id_submission) AS note_moyenne,
              (SELECT COUNT(*)            FROM votes WHERE id_submission = s.id_submission) AS nb_votes,
              COALESCE((SELECT note FROM votes WHERE id_submission = s.id_submission AND id_user = ?), 0) AS ma_note
            FROM submissions s JOIN user u ON u.id_user = s.id_user
            WHERE s.id_challenge = ? ORDER BY $order");
        $s->execute([$uid ?? 0, $cid]);
        return $s->fetchAll();
    }

    public function byUser(int $uid): array
    {
        $s = $this->db->prepare('SELECT s.*, c.titre,
              (SELECT ROUND(AVG(note),1) FROM votes WHERE id_submission = s.id_submission) AS note_moyenne,
              (SELECT COUNT(*) FROM votes WHERE id_submission = s.id_submission) AS nb_votes
            FROM submissions s JOIN challenge c ON c.id_challenge = s.id_challenge
            WHERE s.id_user = ? ORDER BY s.created_at DESC');
        $s->execute([$uid]);
        return $s->fetchAll();
    }

    public function hasSubmitted(int $cid, int $uid): bool
    {
        $s = $this->db->prepare('SELECT 1 FROM submissions WHERE id_challenge = ? AND id_user = ?');
        $s->execute([$cid, $uid]);
        return (bool)$s->fetchColumn();
    }

    public function countByUser(int $uid): int
    {
        $s = $this->db->prepare('SELECT COUNT(*) FROM submissions WHERE id_user = ?');
        $s->execute([$uid]);
        return (int)$s->fetchColumn();
    }

    public function create(int $cid, int $uid, string $desc, ?string $image, ?string $link): int
    {
        $s = $this->db->prepare('INSERT INTO submissions (id_challenge, id_user, description, image, link) VALUES (?,?,?,?,?)');
        $s->execute([$cid, $uid, $desc, $image, $link]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $uid, string $desc, ?string $image, ?string $link): bool
    {
        $s = $this->db->prepare('UPDATE submissions SET description=?, image=?, link=?, updated_at=NOW() WHERE id_submission=? AND id_user=?');
        $s->execute([$desc, $image, $link, $id, $uid]);
        return $s->rowCount() > 0;
    }

    public function delete(int $id, int $uid): bool
    {
        $s = $this->db->prepare('DELETE FROM submissions WHERE id_submission=? AND id_user=?');
        $s->execute([$id, $uid]);
        return $s->rowCount() > 0;
    }

    /** Enregistre (ou modifie) le vote de $uid. Retourne la nouvelle moyenne. */
    public function vote(int $id, int $uid, int $note): array
    {
        $s = $this->db->prepare('INSERT INTO votes (id_submission, id_user, note) VALUES (?,?,?)
                                 ON DUPLICATE KEY UPDATE note = VALUES(note)');
        $s->execute([$id, $uid, $note]);
        $s = $this->db->prepare('SELECT ROUND(AVG(note),1) AS note_moyenne, COUNT(*) AS nb_votes FROM votes WHERE id_submission = ?');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function leaderboard(int $limit = 20): array
    {
        $s = $this->db->prepare('SELECT s.id_submission, s.id_challenge, c.titre, u.nom, u.prenom,
              ROUND(AVG(v.note),2) AS note_moyenne, COUNT(v.id_vote) AS nb_votes
            FROM submissions s
            JOIN votes v ON v.id_submission = s.id_submission
            JOIN challenge c ON c.id_challenge = s.id_challenge
            JOIN user u ON u.id_user = s.id_user
            GROUP BY s.id_submission, s.id_challenge, c.titre, u.nom, u.prenom
            ORDER BY note_moyenne DESC, nb_votes DESC LIMIT ' . (int)$limit);
        $s->execute();
        return $s->fetchAll();
    }
}
