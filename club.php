<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) die("Season ID not provided.");
$season_id = (int)$_GET['id'];
mysqli_query($conn, "UPDATE players SET season_id = $season_id WHERE season_id IS NULL");

// Fetch season info
$season = mysqli_query($conn, "SELECT * FROM seasons WHERE id=$season_id");
$seasonData = mysqli_fetch_assoc($season);
if (!$seasonData) die("Season not found.");

$_SESSION['club_id'] = $season_id;
$_SESSION['club_name'] = $seasonData['club'];

// Fetch players
$sql = "SELECT * FROM players WHERE season_id='$season_id' ORDER BY jersey_no";
$result = mysqli_query($conn, $sql);

// Fetch unique countries for nationality filter
$countryQuery = "SELECT DISTINCT nationality FROM players WHERE season_id='$season_id' ORDER BY nationality ASC";
$countryResult = mysqli_query($conn, $countryQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($seasonData['title']); ?></title>
<link rel="stylesheet" href="styles/create_season.css">
<link rel="stylesheet" href="styles/home-sidebar.css">

<style>
body {
    margin: 20px;
}
h1 {
    text-align: center;
}
.players-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, 200px);
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}
.player-card {
    border: 1px solid white;
    border-radius: 12px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.1);
    text-align: center;
    position: relative;
    width: 200px;
}
.player-pic {
    position: relative;
}
.player-pic img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    object-position: top;
    display: block;
}
.jersey-number {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0,0,0,0.6);
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: bold;
}
.player-info {
    padding: 12px;
}
.player-info p {
    margin: 0;
    font-weight: bold;
    color: white;
}
.flag-container {
    position: absolute;
    top: 5px;
    left: 5px;
    width: 60px;
    height: 40px;
    z-index: 0; 
    opacity: 0.7;
}
.flag {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px;
}
.filter-bar {
    text-align:center;
    margin-bottom: 20px;
    margin-top: 50px;
}
.filter-bar select {
    padding: 6px 10px;
    border-radius: 6px;
    border: none;
    font-size: 16px;
    background: rgba(255,255,255,0.1);
    color: white;
    margin: 0 10px;
}
.filter-bar option {
    background: #14233f;
    color: white;
}
</style>
</head>
<body>

<h1><?php echo htmlspecialchars($seasonData['title']); ?></h1>
<?php include 'backtoindex.php'; ?>

<div class="main-content">
<?php include 'home_sidebar.php'; ?>

<!-- Filters -->
<div class="filter-bar">
    <label for="positionFilter" style="color:white; font-weight:bold;">Filter by Position:</label>
    <select id="positionFilter">
        <option value="all">All</option>
        <option value="Goalkeeper">Goalkeepers</option>
        <option value="Defender">Defenders</option>
        <option value="Midfielder">Midfielders</option>
        <option value="Forward">Forwards</option>
    </select>

    <label for="countryFilter" style="color:white; font-weight:bold;">Filter by Nationality:</label>
    <select id="countryFilter">
        <option value="all">All</option>
        <?php while($row = mysqli_fetch_assoc($countryResult)): ?>
            <option value="<?php echo htmlspecialchars($row['nationality']); ?>">
                <?php echo htmlspecialchars($row['nationality']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<!-- Players -->
<div class="players-grid">
    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <?php while($player = mysqli_fetch_assoc($result)) { ?>
            <div class="player-card" 
                 data-category="<?php echo htmlspecialchars($player['category']); ?>" 
                 data-country="<?php echo htmlspecialchars($player['nationality']); ?>">
                <div class="flag-container">
                    <img src="<?php echo $player['flag_picture']; ?>" class="flag" alt="Flag">
                </div>
                <div class="player-pic">
                    <img src="<?php echo $player['picture']; ?>" alt="<?php echo htmlspecialchars($player['name']); ?>">
                    <span class="jersey-number"><?php echo $player['jersey_no']; ?></span>
                </div>
                <div class="player-info">
                    <p><?php echo htmlspecialchars($player['name']); ?></p>
                    <p style="font-size:14px; color:#ccc;"><?php echo htmlspecialchars($player['category']); ?></p>
                </div>
            </div>
        <?php } ?>
    <?php else: ?>
        <p style="color:white; text-align:center; width:100%;">No player data available.</p>
    <?php endif; ?>
</div>
</div>

<script>
function filterPlayers() {
    let positionFilter = document.getElementById("positionFilter").value;
    let countryFilter = document.getElementById("countryFilter").value;
    let players = document.querySelectorAll(".player-card");

    players.forEach(player => {
        let position = player.getAttribute("data-category");
        let country = player.getAttribute("data-country");

        let matchesPosition = (positionFilter === "all" || position === positionFilter);
        let matchesCountry = (countryFilter === "all" || country === countryFilter);

        if (matchesPosition && matchesCountry) {
            player.style.display = "block";
        } else {
            player.style.display = "none";
        }
    });
}

document.getElementById("positionFilter").addEventListener("change", filterPlayers);
document.getElementById("countryFilter").addEventListener("change", filterPlayers);
</script>

</body>
</html>
