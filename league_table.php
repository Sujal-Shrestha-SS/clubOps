<?php
session_start();
include 'db.php';

// $season_id = $_SESSION['club_id'];
$season_id = (int)$_GET['id'];
// Choose the club you want stats for
$club_name = $_SESSION['club_name']; // You can make this dynamic with a dropdown if needed

include 'admin_sessioncheck.php';
// Initialize values
$wins = $draws = $losses = $goals_scored = $goals_conceded = 0;

// Fetch fixtures where club played (either home or away)
$sql = "SELECT * FROM fixtures 
        WHERE (home_team='$club_name' OR away_team='$club_name') 
        AND status='Match Completed'
        AND season_id=$season_id";
$result = $conn->query($sql);

$idCheck = "SELECT COUNT(id) AS total_id FROM fixtures WHERE season_id = $season_id";
$idResult = mysqli_query($conn, $idCheck);
$idCount = mysqli_fetch_assoc($idResult);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $home = $row['home_team'];
        $away = $row['away_team'];
        $home_score = (int)$row['home_score'];
        $away_score = (int)$row['away_score'];

        if ($home === $club_name) {
            $goals_scored += $home_score;
            $goals_conceded += $away_score;

            if ($home_score > $away_score) $wins++;
            elseif ($home_score == $away_score) $draws++;
            else $losses++;
        } else {
            $goals_scored += $away_score;
            $goals_conceded += $home_score;

            if ($away_score > $home_score) $wins++;
            elseif ($away_score == $home_score) $draws++;
            else $losses++;
        }
    }
}

$goal_difference = $goals_scored - $goals_conceded;
$points = ($wins * 3) + ($draws * 1);

// Handle position input
// $position = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_position'])) {
    $position = intval($_POST['position']);

    $sql = "UPDATE seasons SET position = '$position' WHERE id = '$season_id' ";

    mysqli_query($conn, $sql);
}

    $pos_query = "SELECT * FROM seasons WHERE id = '$season_id' ";
    $pos_result = mysqli_query($conn, $pos_query);
    $pos_output = mysqli_fetch_assoc($pos_result);


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_club'])) {
    $club = $_POST['club'];

    $sql = "UPDATE seasons SET club = '$club' WHERE id = '$season_id' ";

    mysqli_query($conn, $sql);
}

    $club_query = "SELECT * FROM seasons WHERE id = '$season_id' ";
    $club_result = mysqli_query($conn, $club_query);
    $club_output = mysqli_fetch_assoc($club_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>League Table</title>
    <link rel="stylesheet" href="styles/create_season.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <style>
        body {
            margin: 30px;
        }
        .stats-box {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid white;
            padding: 20px;
            margin: 20px auto;
            border-radius: 10px;
            width: 50%;
            text-align: left;
        }
        .position-form, .club-form {
            margin-top: 20px;
        }
        input[type="number"], input[type="text"] {
            padding: 6px;
            border-radius: 5px;
            border: none;
        }

        label{
            display: inline-block;
            width: 120px;
        }
        button {
            padding: 6px 12px;
            background-color: #0073e6;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #005bb5;
        }

        h1 {
            margin-bottom: 20px;   /* space below the text */
            line-height: 1.5;      /* adjusts vertical spacing */
        }

    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
    <h1><?php echo $club_name; ?> - League Stats</h1>
    <?php include 'backtohome.php'; ?>

    <div class="stats-box">
        <?php if ($pos_output['club'] !== null): ?>
            <p><strong>Club:</strong> <?php echo $club_output['club']; ?></p>
        <?php endif; ?>
        <?php if ($pos_output['position'] !== null): ?>
            <p><strong>Position:</strong> <?php echo $pos_output['position']; ?></p>
        <?php endif; ?>
        <p><strong>Matches:</strong> <?php echo $idCount['total_id']; ?></p>
        <p><strong>Wins:</strong> <?php echo $wins; ?></p>
        <p><strong>Draws:</strong> <?php echo $draws; ?></p>
        <p><strong>Losses:</strong> <?php echo $losses; ?></p>
        <p><strong>Goals Scored:</strong> <?php echo $goals_scored; ?></p>
        <p><strong>Goals Conceded:</strong> <?php echo $goals_conceded; ?></p>
        <p><strong>Goal Difference:</strong> <?php echo $goal_difference; ?></p>
        <p><strong>Points:</strong> <?php echo $points; ?></p>
        <form method="post" class="club-form">
            <label for="club"><strong>Enter Club:</strong></label>
            <input type="text" name="club" id="club" required>
            <button type="submit" name="save_club">Save Club</button>
        </form>
        <form method="post" class="position-form">
            <label for="position"><strong>Enter Position:</strong></label>
            <input type="number" name="position" id="position" required>
            <button type="submit" name="save_position">Save Position</button>
        </form>
        
    </div>
    </div>
</body>
</html>
