# ⚽ Football Club Management System

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

A full-stack web application for managing a football club's seasons, players, fixtures, match results, and performance statistics — built with PHP and MySQL.

---

## 📋 Description

The **Football Club Management System - ClubOps** is a data-driven web application designed to help football club administrators and analysts track everything that matters across a season. From registering players and scheduling fixtures to recording match results and visualising performance metrics, the system provides a centralised dashboard for all club-related data.

It features a **dual-access model**: a public-facing view for fans and viewers, and a password-protected admin panel for club staff to manage data securely.

---

## ✨ Features

### 🔐 Admin Panel
- Secure session-based admin login and logout
- Protected routes — unauthorised users are redirected automatically

### 🗓️ Season Management
- Create and manage multiple club seasons
- Associate players, fixtures, and stats with individual seasons

### 👤 Player Management
- Add, edit, and delete players with profile images and nationality flags
- Filter players by position (GK, Defender, Midfielder, Forward) or nationality
- Store jersey numbers, categories, and positions

### 📅 Fixture Management
- Create fixtures by entering home and away teams
- Insert match results (home score / away score)
- Track fixture status: *Pending* or *Match Completed*
- Delete fixtures and reset auto-increment IDs
- Filter fixtures by venue (Home/Away) and result (Win/Draw/Loss)

### 📊 Match Statistics
- Enter goal scorers and assist providers per fixture
- Associate each goal with the home or away team
- Update or delete goal stats per fixture
- Visual tick indicator on fixtures where stats have been fully entered

### 📈 Club Performance
- Overall club stats: wins, draws, losses, goals scored/conceded, goal difference, points
- Separate breakdown for home and away performance
- Record and display final league position and club name

### 🧑‍🤝‍🧑 Player Performance & Statistics
- Individual player goal and assist totals across a season
- Ranked leaderboards for Goals, Assists, and Goal Contributions (G/A)
- Player card grid view with profile images
- Top 3 players highlighted with gold styling

### 🔗 Player Combination Tracker
- Select any two players and view every fixture where they linked up (scorer + assist provider)
- Displays total linkups, fixtures involved, and individual goal details

### 🏆 League Table / Club Info
- Record and display club's league position
- Admin can enter and save club name and final standing
- Full stats summary including matches played and points

---



## 🛠️ Tech Stack

| Layer       | Technology                        |
|-------------|-----------------------------------|
| Backend     | PHP 8+ (procedural)               |
| Database    | MySQL via `mysqli`                |
| Frontend    | HTML5, CSS3, Vanilla JavaScript   |
| Styling     | Custom CSS with glassmorphism UI  |
| Sessions    | PHP native session management     |
| Server      | Apache (XAMPP / WAMP recommended) |

---

## 📁 Project Structure

```
football-club-management/
│
├── index.php                    # Public homepage — lists all seasons
├── admin_login.php              # Admin login form
├── admin_sessioncheck.php       # Session guard (included on protected pages)
├── logout.php                   # Destroys session and redirects
├── home.php                     # Admin dashboard — season management
│
├── club.php                     # Public player list for a season
├── club_fixtures.php            # Public fixture list with filters
├── club_performance.php         # Public club performance stats
├── club_player_performance.php  # Public player card grid with G/A stats
├── club_player_statistics.php   # Public ranked leaderboards (Goals/Assists/G/A)
├── club_player_combination.php  # Public player linkup tracker
│
├── season.php                   # Admin: manage fixtures for a season
├── fixture_details.php          # Admin: enter/edit goal and assist stats
├── insert_players.php           # Admin: add/edit/delete players
├── player_stats.php             # Admin: player stats overview
├── league_table.php             # Admin: set club info and league position
│
├── home_sidebar.php             # Sidebar navigation (public views)
├── sidebar.php                  # Sidebar navigation (admin views)
│
├── db.php                       # Database connection
│
├── styles/
│   ├── create_season.css        # Global base styles
│   ├── home-sidebar.css         # Public sidebar styles
│   ├── sidebar.css              # Admin sidebar styles
│   └── season.css               # Fixture page styles
│
└── uploads/                     # Uploaded player and flag images
```

---


## ⚙️ Installation & Setup

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) or [WAMP](https://www.wampserver.com/) (or any Apache + PHP + MySQL stack)
- PHP 8.0+
- MySQL 5.7+

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/Sujal-Shrestha-SS/clubOps.git
```

**2. Move to your web server's root directory**
```bash
# For XAMPP (Windows):
cp -r football-club-management/ C:/xampp/htdocs/

# For XAMPP (macOS):
cp -r football-club-management/ /Applications/XAMPP/htdocs/
```

**3. Create the database**

Open **phpMyAdmin** (or your MySQL client) and create a new database:
```sql
CREATE DATABASE efootball;
```

Then import the provided SQL schema (if included), or manually create the required tables:

```sql
CREATE TABLE seasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  club VARCHAR(255),
  position INT
);

CREATE TABLE players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  jersey_no INT,
  nationality VARCHAR(100),
  category VARCHAR(50),
  position VARCHAR(10),
  picture VARCHAR(255),
  flag_picture VARCHAR(255),
  season_id INT
);

CREATE TABLE fixtures (
  id INT AUTO_INCREMENT PRIMARY KEY,
  season_id INT,
  home_team VARCHAR(255),
  away_team VARCHAR(255),
  home_score INT DEFAULT 0,
  away_score INT DEFAULT 0,
  status VARCHAR(50) DEFAULT 'Pending'
);

CREATE TABLE goal_stats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fixture_id INT,
  season_id INT,
  team VARCHAR(10),
  goal_scorer VARCHAR(255),
  assist_provider VARCHAR(255),
  status VARCHAR(50)
);
```

**4. Configure the database connection**

Open `db.php` and update credentials if needed:
```php
$host = "localhost";
$user = "root";
$pw   = "";          // your MySQL password
$db   = "efootball";
```

**5. Create the uploads directory**
```bash
mkdir uploads
chmod 755 uploads
```

**6. Start your server and open the app**

Navigate to:
```
http://localhost/football-club-management/
```

---