-- CreatiZone : schéma unique et cohérent. Nouvelle base => votre ancienne base n'est pas touchée.
CREATE DATABASE IF NOT EXISTS creatizone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE creatizone;

CREATE TABLE IF NOT EXISTS user (
  id_user     INT AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(80)  NOT NULL,
  prenom      VARCHAR(80)  NOT NULL,
  email       VARCHAR(190) NOT NULL UNIQUE,
  motdepasse  VARCHAR(255) NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS challenge (
  id_challenge  INT AUTO_INCREMENT PRIMARY KEY,
  id_user       INT NOT NULL,
  titre         VARCHAR(150) NOT NULL,
  description   TEXT NOT NULL,
  categorie     VARCHAR(50)  NOT NULL,
  deadline      DATE NOT NULL,
  image         VARCHAR(255) NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS submissions (
  id_submission INT AUTO_INCREMENT PRIMARY KEY,
  id_challenge  INT NOT NULL,
  id_user       INT NOT NULL,
  description   TEXT NOT NULL,
  image         VARCHAR(255) NULL,
  link          VARCHAR(500) NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_one_per_challenge (id_challenge, id_user),
  FOREIGN KEY (id_challenge) REFERENCES challenge(id_challenge) ON DELETE CASCADE,
  FOREIGN KEY (id_user)      REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS votes (
  id_vote       INT AUTO_INCREMENT PRIMARY KEY,
  id_submission INT NOT NULL,
  id_user       INT NOT NULL,
  note          TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_one_vote (id_submission, id_user),
  FOREIGN KEY (id_submission) REFERENCES submissions(id_submission) ON DELETE CASCADE,
  FOREIGN KEY (id_user)       REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comments (
  id_comment    INT AUTO_INCREMENT PRIMARY KEY,
  id_submission INT NOT NULL,
  id_user       INT NOT NULL,
  content       VARCHAR(1000) NOT NULL,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_submission) REFERENCES submissions(id_submission) ON DELETE CASCADE,
  FOREIGN KEY (id_user)       REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comment_likes (
  id_comment INT NOT NULL,
  id_user    INT NOT NULL,
  PRIMARY KEY (id_comment, id_user),
  FOREIGN KEY (id_comment) REFERENCES comments(id_comment) ON DELETE CASCADE,
  FOREIGN KEY (id_user)    REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS badges (
  id_badge    INT PRIMARY KEY,
  nom         VARCHAR(80)  NOT NULL,
  description VARCHAR(255) NOT NULL,
  icone       VARCHAR(10)  NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_badges (
  id_user    INT NOT NULL,
  id_badge   INT NOT NULL,
  awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_user, id_badge),
  FOREIGN KEY (id_user)  REFERENCES user(id_user) ON DELETE CASCADE,
  FOREIGN KEY (id_badge) REFERENCES badges(id_badge) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO badges (id_badge, nom, description, icone) VALUES
 (1, 'Première participation', 'Vous avez publié votre première participation.', '🥇'),
 (2, '10 participations',      'Vous avez publié 10 participations.',            '🏆'),
 (3, 'Participation populaire','Une de vos participations a reçu 5 votes ou plus.', '⭐');
