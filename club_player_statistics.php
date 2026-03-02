<?php
session_start();
include 'db.php';

$season_id = $_SESSION['club_id']; // Current season

// --- Fetch Goals ---
$sql_goals = "
    SELECT p.name, p.picture, COALESCE(SUM(g.goals),0) AS total_goals
    FROM players p
    LEFT JOIN (
        SELECT goal_scorer AS player, COUNT(*) AS goals
        FROM goal_stats g
        JOIN fixtures f ON g.fixture_id = f.id
        WHERE f.season_id = '$season_id'
        GROUP BY goal_scorer
    ) g ON p.name = g.player
    WHERE p.season_id='$season_id'
    GROUP BY p.id, p.name, p.picture
    ORDER BY total_goals DESC
";
$result_goals = mysqli_query($conn, $sql_goals);

$players_goals = [];
if ($result_goals && mysqli_num_rows($result_goals) > 0) {
    while($row = mysqli_fetch_assoc($result_goals)) {
        $players_goals[] = ['name'=>$row['name'], 'picture'=>$row['picture'], 'goals'=>$row['total_goals']];
    }
}

// --- Fetch Assists ---
$sql_assists = "
    SELECT p.name, p.picture, COALESCE(SUM(a.assists),0) AS total_assists
    FROM players p
    LEFT JOIN (
        SELECT assist_provider AS player, COUNT(*) AS assists
        FROM goal_stats g
        JOIN fixtures f ON g.fixture_id = f.id
        WHERE f.season_id = '$season_id'
        GROUP BY assist_provider
    ) a ON p.name = a.player
    WHERE p.season_id='$season_id'
    GROUP BY p.id, p.name, p.picture
    ORDER BY total_assists DESC
";
$result_assists = mysqli_query($conn, $sql_assists);

$players_assists = [];
if ($result_assists && mysqli_num_rows($result_assists) > 0) {
    while($row = mysqli_fetch_assoc($result_assists)) {
        $players_assists[] = ['name'=>$row['name'], 'picture'=>$row['picture'], 'assists'=>$row['total_assists']];
    }
}

// --- Fetch G/A (Goals + Assists) ---
$sql_ga = "
    SELECT p.name, p.picture,
           COALESCE(SUM(g.goals),0) AS total_goals,
           COALESCE(SUM(a.assists),0) AS total_assists,
           (COALESCE(SUM(g.goals),0) + COALESCE(SUM(a.assists),0)) AS ga
    FROM players p
    LEFT JOIN (
        SELECT goal_scorer AS player, COUNT(*) AS goals
        FROM goal_stats g
        JOIN fixtures f ON g.fixture_id = f.id
        WHERE f.season_id = '$season_id'
        GROUP BY goal_scorer
    ) g ON p.name = g.player
    LEFT JOIN (
        SELECT assist_provider AS player, COUNT(*) AS assists
        FROM goal_stats g
        JOIN fixtures f ON g.fixture_id = f.id
        WHERE f.season_id = '$season_id'
        GROUP BY assist_provider
    ) a ON p.name = a.player
    WHERE p.season_id='$season_id'
    GROUP BY p.id, p.name, p.picture
    ORDER BY ga DESC, total_goals DESC
";
$result_ga = mysqli_query($conn, $sql_ga);

$players_ga = [];
if ($result_ga && mysqli_num_rows($result_ga) > 0) {
    while($row = mysqli_fetch_assoc($result_ga)) {
        $players_ga[] = ['name'=>$row['name'], 'picture'=>$row['picture'], 'ga'=>$row['ga']];
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Player Statistics</title>
<link rel="stylesheet" href="styles/create_season.css">
<link rel="stylesheet" href="styles/home-sidebar.css">
<style>
body {
    
    margin: 20px;
}

h1 {
    text-align: center;
}

.stats-container {
    display: flex;
    gap: 20px;
    margin-top: 20px;
    flex-wrap: wrap;
    margin-top: 50px;
}

.stat-box {
    flex: 1;
    min-width: 250px;
    background: rgba(255,255,255,0.1);
    border: 1px solid white;
    border-radius: 12px;
    padding: 10px;
    color: white;
    max-height: 500px;
    overflow-y: auto;
}

.stat-box h3 {
    text-align: center;
    margin-bottom: 10px;
    font-size: 30px;
}

.stat-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 10px;
}

.stat-row .rank {
    font-weight: 500;
    margin-right: 10px;
    font-size: 25px;
    width: 25px;
    text-align: right;
}

.stat-row.top-player .rank{
    font-size: 30px;
    font-weight: bold;
}


.stat-row.even {
    background: rgba(255,255,255,0.05);
}

.stat-row.odd {
    background: rgba(255,255,255,0.15);
}

.stat-row.top-player .player-name {
    font-weight: bold;
    font-size: 18px;
}

.stat-row img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    object-position: top;
    margin-right: 10px;
    
    
}
.stat-row .player-name {
    flex: 1;
    font-size: 16px;
    font-weight: 500;
    text-align: left;
}


.stat-number {
    font-weight: bold;
    font-size: 50px;
    margin-left: -35px;
    
}



.top-player .stat-number {
    color: gold;
    text-shadow: 0 0 8px rgba(255, 215, 0, 0.7);
}

</style>
</head>
<body>

<h1><?php echo $_SESSION['club_name']; ?> - Player Statistics</h1>
<?php include 'backtoindex.php'; ?>

<div class="main-content">
    <?php include 'home_sidebar.php'; ?>
    <?php if (!empty($players_goals)): ?>
    <div class="stats-container">
        <!-- Goals -->
        <div class="stat-box">
            <h3>Goals</h3>
            <?php foreach ($players_goals as $index => $player): ?>
                <div class="stat-row <?php echo $index % 2 === 0 ? 'even' : 'odd'; ?> <?php echo $index < 3 ? 'top-player' : ''; ?>">
                    <span class="rank"><?php echo $index + 1; ?>.</span>
                    <img src="<?php echo $player['picture']; ?>" alt="<?php echo htmlspecialchars($player['name']); ?>">
                    <span class="player-name"><?php echo htmlspecialchars($player['name']); ?></span>
                    <span class="stat-number"><?php echo $player['goals']; ?></span>
                </div>
<?php endforeach; ?>

        </div>

        <!-- Assists -->
        <div class="stat-box">
            <h3>Assists</h3>
            <?php foreach ($players_assists as $index => $player): ?>
                <div class="stat-row <?php echo $index % 2 === 0 ? 'even' : 'odd'; ?> <?php echo $index < 3 ? 'top-player' : ''; ?>">
                    <span class="rank"><?php echo $index + 1; ?>.</span>
                    <img src="<?php echo $player['picture']; ?>" alt="<?php echo htmlspecialchars($player['name']); ?>">
                    <span class="player-name"><?php echo htmlspecialchars($player['name']); ?></span>
                    <span class="stat-number"><?php echo $player['assists']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- G/A -->
        <div class="stat-box">
            <h3>G/A</h3>
            <?php foreach ($players_ga as $index => $player): ?>
                <div class="stat-row <?php echo $index % 2 === 0 ? 'even' : 'odd'; ?> <?php echo $index < 3 ? 'top-player' : ''; ?>">
                    <span class="rank"><?php echo $index + 1; ?>.</span>
                    <img src="<?php echo $player['picture']; ?>" alt="<?php echo htmlspecialchars($player['name']); ?>">
                    <span class="player-name"><?php echo htmlspecialchars($player['name']); ?></span>
                    <span class="stat-number"><?php echo $player['ga']; ?></span>
                </div>
<?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
        <p style="text-align:center; margin-top: 50px">No player statistics available.</p>
    <?php endif; ?>
</div>
</body>
</html>
