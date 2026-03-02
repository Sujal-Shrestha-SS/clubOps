<?php
session_start();
include 'db.php';


$season_id = $_SESSION['club_id']; // Current season ID


// Fetch players with stats (joined with players table)
$sql = "
    SELECT p.id, p.name, p.jersey_no, p.nationality, p.picture,
           COALESCE(SUM(goals),0) AS total_goals,
           COALESCE(SUM(assists),0) AS total_assists,
           (COALESCE(SUM(goals),0) + COALESCE(SUM(assists),0)) AS ga
    FROM players p
    LEFT JOIN (
        SELECT goal_scorer AS player, COUNT(*) AS goals, 0 AS assists
        FROM goal_stats g
        JOIN fixtures f ON g.fixture_id = f.id
        WHERE f.season_id = '$season_id'
        GROUP BY goal_scorer
        UNION ALL
        SELECT assist_provider AS player, 0 AS goals, COUNT(*) AS assists
        FROM goal_stats g
        JOIN fixtures f ON g.fixture_id = f.id
        WHERE f.season_id = '$season_id'
        GROUP BY assist_provider
    ) stats ON p.name = stats.player
     WHERE p.season_id = '$season_id'
    GROUP BY p.id, p.name, p.jersey_no, p.nationality, p.picture
    ORDER BY jersey_no;
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Player Stats</title>
    <link rel="stylesheet" href="styles/create_season.css">
    <link rel="stylesheet" href="styles/home-sidebar.css">
    <style>
        body {
            margin: 20px;
        }
        h1 {
            text-align: center;
            /* margin-bottom: 30px;
            line-height: 1.5; */
        }
        .player-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr); /* Always 4 per row on large screens */
            gap: 20px;
        }
        @media (max-width: 1200px) {
            .player-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 900px) {
            .player-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 600px) {
            .player-grid {
                grid-template-columns: 1fr;
            }
        }
        .player-card {
            border: 1px solid white;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.1);
            transition: transform 0.2s ease;
        }
        .player-card:hover {
            transform: scale(1.05);
        }
        .player-card img {
            width: 100%;
            height: 270px;
            object-fit: cover;
            object-position: top; /* Show player face (top of image) */
            border-bottom: 1px solid white;
        }
        .player-info {
            padding: 12px;
        }
        .player-info p {
            margin: 6px 0;
        }
        .player-info strong {
            font-size: 1.1em;
        }

        
    </style>
</head>
<body>

    <h1><?php echo $_SESSION['club_name']; ?> - Player Statistics</h1>
    <?php include 'backtoindex.php'; ?>

    <div class="main-content">
    <?php include 'home_sidebar.php'; ?>

    <h3 style="margin-top: 50px">Player Stats</h3>

        <div class="player-grid">
            <?php if(mysqli_num_rows($result) === 0) { ?>
    <p style="text-align:center; font-size:18px; color:#fff; margin-top:20px;">No players available.</p>
<?php } else { ?>
    <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <div class="player-card">
            <img src="<?php echo $row['picture']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
            <div class="player-info">
                <p><strong><?php echo htmlspecialchars($row['name']); ?></strong></p>
                <p>Goals: <?php echo $row['total_goals']; ?></p>
                <p>Assists: <?php echo $row['total_assists']; ?></p>
                <p>G/A: <?php echo $row['ga']; ?></p>
            </div>
        </div>
    <?php } ?>
<?php } ?>

<!-- No players available wont appear in center because the parent is a grid container -->

</div>

    </div>
</body>
</html>
