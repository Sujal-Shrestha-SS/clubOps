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