<?php
class Badge
{
    public function __construct(private PDO $db) {}

    /** Tous les badges, avec la date d'obtention si l'utilisateur les a. */
    public function forUser(int $uid): array
    {
        $s = $this->db->prepare('SELECT b.*, ub.awarded_at FROM badges b
            LEFT JOIN user_badges ub ON ub.id_badge = b.id_badge AND ub.id_user = ?
            ORDER BY b.id_badge');
        $s->execute([$uid]);
        return $s->fetchAll();
    }

    private function award(int $uid, int $badgeId): bool
    {
        $s = $this->db->prepare('INSERT IGNORE INTO user_badges (id_user, id_badge) VALUES (?,?)');
        $s->execute([$uid, $badgeId]);
        return $s->rowCount() > 0;
    }

    private function name(int $badgeId): string
    {
        $s = $this->db->prepare('SELECT CONCAT(icone, " ", nom) FROM badges WHERE id_badge = ?');
        $s->execute([$badgeId]);
        return (string)$s->fetchColumn();
    }

    /** À appeler après une nouvelle participation. */
    public function afterSubmission(int $uid): array
    {
        $n = (new Submission($this->db))->countByUser($uid);
        $new = [];
        if ($n >= 1  && $this->award($uid, 1)) $new[] = $this->name(1);
        if ($n >= 10 && $this->award($uid, 2)) $new[] = $this->name(2);
        return $new;
    }

    /** À appeler après un vote : le badge va au PROPRIÉTAIRE de la participation. */
    public function afterVote(int $submissionId): void
    {
        $s = $this->db->prepare('SELECT s.id_user, (SELECT COUNT(*) FROM votes WHERE id_submission = s.id_submission) AS nb
                                 FROM submissions s WHERE s.id_submission = ?');
        $s->execute([$submissionId]);
        $r = $s->fetch();
        if ($r && (int)$r['nb'] >= 5) $this->award((int)$r['id_user'], 3);
    }
}
