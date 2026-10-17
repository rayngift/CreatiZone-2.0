<?php
class Comment
{
    public function __construct(private PDO $db) {}

    public function forSubmission(int $sid, ?int $uid): array
    {
        $s = $this->db->prepare('SELECT c.*, u.nom, u.prenom,
              (SELECT COUNT(*) FROM comment_likes l WHERE l.id_comment = c.id_comment) AS likes,
              EXISTS(SELECT 1 FROM comment_likes l WHERE l.id_comment = c.id_comment AND l.id_user = ?) AS liked
            FROM comments c JOIN user u ON u.id_user = c.id_user
            WHERE c.id_submission = ? ORDER BY c.created_at ASC');
        $s->execute([$uid ?? 0, $sid]);
        return $s->fetchAll();
    }

    public function add(int $sid, int $uid, string $content): int
    {
        $s = $this->db->prepare('INSERT INTO comments (id_submission, id_user, content) VALUES (?,?,?)');
        $s->execute([$sid, $uid, $content]);
        return (int)$this->db->lastInsertId();
    }

    public function delete(int $id, int $uid): bool
    {
        $s = $this->db->prepare('DELETE FROM comments WHERE id_comment = ? AND id_user = ?');
        $s->execute([$id, $uid]);
        return $s->rowCount() > 0;
    }

    public function toggleLike(int $id, int $uid): ?array
    {
        $s = $this->db->prepare('SELECT 1 FROM comments WHERE id_comment = ?');
        $s->execute([$id]);
        if (!$s->fetchColumn()) return null;

        $del = $this->db->prepare('DELETE FROM comment_likes WHERE id_comment = ? AND id_user = ?');
        $del->execute([$id, $uid]);
        $liked = $del->rowCount() === 0;
        if ($liked) {
            $this->db->prepare('INSERT INTO comment_likes (id_comment, id_user) VALUES (?,?)')->execute([$id, $uid]);
        }
        $c = $this->db->prepare('SELECT COUNT(*) FROM comment_likes WHERE id_comment = ?');
        $c->execute([$id]);
        return ['liked' => $liked, 'likes' => (int)$c->fetchColumn()];
    }
}
