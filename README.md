# 🌿 CreatiZone — Challenge Hub

> **Create, compete, shine.** A community web platform where users create challenges, submit their entries, vote, comment and earn badges.

![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-vanilla-F7DF1E?logo=javascript&logoColor=black)
![Status](https://img.shields.io/badge/status-active-success)

The interface is in **French**; the code and this documentation are in English.

<!-- Add screenshots here:
![Home](docs/home.png)
![Discover](docs/discover.png)
-->

---

## ✨ Features

| Area | What you can do |
|---|---|
| **Accounts** | Register / log in with an animated sliding form. Passwords are hashed (`password_hash`). |
| **Challenges** | Create, edit and delete your own challenges (title, description, category, deadline, image). |
| **Discover** | Search, filter by category, sort by newest, deadline, popularity or rating. |
| **Participations** | One entry per challenge (text, link, image). Edit or delete your own. Blocked after the deadline. |
| **Voting** | Rate entries from 1 to 5 stars (you can change your vote, but not vote for yourself). |
| **Comments** | Add, delete and like comments, all without reloading the page (AJAX). |
| **Badges** | 🥇 First participation · 🏆 10 participations · ⭐ Popular entry (5+ votes). |
| **Leaderboard** | Top-rated entries across the platform. |
| **Profile** | Your badges and your participations. |

## 🛠️ Tech stack

- **Backend:** PHP 8.1+ (procedural pages + small model classes), PDO with prepared statements
- **Database:** MySQL / MariaDB (InnoDB, foreign keys, unique constraints)
- **Frontend:** HTML5, CSS3, vanilla JavaScript (`fetch`)
- **Local server:** WAMP / XAMPP / any Apache + PHP + MySQL stack

## 📁 Project structure

```
creatizone/
├── index.php              # Landing page
├── login.php              # Login + registration
├── logout.php
├── discover.php           # Challenge list with filters
├── defi.php               # Challenge page: entries, votes, comments
├── participation.php      # Create / update / delete an entry
├── mes_defis.php          # Manage your own challenges
├── profile.php            # Profile and badges
├── classement.php         # Leaderboard
├── vote.php               # JSON API – voting
├── comment.php            # JSON API – comments (add / delete / like)
├── classes/               # User, Challenge, Submission, Comment, Badge
├── config/database.php    # Single PDO connection
├── includes/              # bootstrap (session, CSRF, helpers), header, footer
├── assets/                # CSS, JS, images
├── uploads/               # User images (not tracked by Git)
└── sql/schema.sql         # Database schema
```

## 🚀 Installation

### Requirements
- PHP **8.1 or newer** with the `pdo_mysql` and `fileinfo` extensions
- MySQL 8+ or MariaDB 10.4+
- Apache (WAMP / XAMPP are fine)

### Steps

1. **Clone the repository** into your web server folder:
   ```bash
   cd C:/wamp64/www        # or htdocs for XAMPP
   git clone https://github.com/<your-username>/creatizone.git
   ```

2. **Create the database.** Open phpMyAdmin (`http://localhost/phpmyadmin`) → **Import** tab → choose `sql/schema.sql` → **Import**.
   This creates a database named `creatizone` with all tables and the 3 default badges.

   Or from the command line:
   ```bash
   mysql -u root -p < sql/schema.sql
   ```

3. **Configure the connection** if needed, in `config/database.php`:
   ```php
   $host = 'localhost';
   $name = 'creatizone';
   $user = 'root';
   $pass = '';      // set your MySQL password here
   ```

4. **Make sure `uploads/` is writable** by the web server.

5. **Open the site:** `http://localhost/creatizone/`

## 🗄️ Database overview

| Table | Purpose |
|---|---|
| `user` | Accounts (`id_user`, `nom`, `prenom`, `email`, `motdepasse`) |
| `challenge` | Challenges created by users |
| `submissions` | Entries to a challenge (unique per user + challenge) |
| `votes` | Ratings 1–5 (unique per user + entry) |
| `comments` / `comment_likes` | Comments and their likes |
| `badges` / `user_badges` | Badge definitions and who earned them |

Naming is consistent everywhere: `id_user`, `id_challenge`, `id_submission`, `id_comment`, `id_badge`.

## 🔒 Security

- SQL injection: prepared statements everywhere
- XSS: all output escaped with `htmlspecialchars`; comments inserted with `textContent`
- CSRF: token checked on every form and API call
- Sessions: ID regenerated on login; the user always comes from the session, never from the client
- Uploads: real MIME check, size limit, random file names, script execution disabled in `uploads/`
- Authorization: only the owner can edit or delete their challenges, entries and comments

> ⚠️ Before deploying publicly: use HTTPS, create a dedicated MySQL user (not `root`), and disable `display_errors`.

## 🗺️ Roadmap

- [ ] Password reset by email
- [ ] Admin / moderator role
- [ ] Pagination on the challenge list
- [ ] Notifications when someone comments or votes
- [ ] Rate limiting on login and voting

## 🤝 Contributing

1. Fork the project
2. Create a branch: `git checkout -b feature/my-feature`
3. Commit your changes: `git commit -m "Add my feature"`
4. Push: `git push origin feature/my-feature`
5. Open a Pull Request

## 📄 License

Choose a license for your project (for example [MIT](https://choosealicense.com/licenses/mit/)) and add a `LICENSE` file.

## 👤 Author

**CreatiZone team** — Bizerte, Tunisia
Contact: contactcreatiZone@gmail.com
