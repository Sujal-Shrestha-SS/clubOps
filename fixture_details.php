<?php
session_start();

include 'db.php';
$season_id = $_SESSION['club_id'];
if (!isset($_GET['id'])) die("Fixture ID missing.");
$fixture_id = (int)$_GET['id'];

include 'admin_sessioncheck.php';

// Fetch fixture info
$fixture = mysqli_query($conn, "SELECT * FROM fixtures WHERE id=$fixture_id");
$fixtureData = mysqli_fetch_assoc($fixture);
if (!$fixtureData) die("Fixture not found.");

// Handle fixture info update
if (isset($_POST['update_fixture'])) {
    $home_team = trim(mysqli_real_escape_string($conn, $_POST['home_team']));
    $away_team = trim(mysqli_real_escape_string($conn, $_POST['away_team']));
    $status = trim(mysqli_real_escape_string($conn, $_POST['status']));

    mysqli_query($conn, "UPDATE fixtures 
                         SET home_team='$home_team', away_team='$away_team' 
                         WHERE id='$fixture_id'");
    header("Location: fixture_details.php?id=$fixture_id");
    exit;
}

// Handle result update
if (isset($_POST['update_result'])) {
    $home_score = (int)$_POST['home_score'];
    $away_score = (int)$_POST['away_score'];
    mysqli_query($conn, "UPDATE fixtures 
                         SET home_score='$home_score', away_score='$away_score', status='Match Completed' 
                         WHERE id='$fixture_id'");
    header("Location: fixture_details.php?id=$fixture_id");
    exit;
}

// Handle initial goal/assist stats insertion
if (isset($_POST['submit_stats'])) {
    $total_goals = (int)$_POST['total_goals'];
    for ($i = 1; $i <= $total_goals; $i++) {
        $goal_scorer = trim(mysqli_real_escape_string($conn, $_POST["goal_scorer_$i"]));
        $assist_provider = trim(mysqli_real_escape_string($conn, $_POST["assist_provider_$i"]));
        $team = trim(mysqli_real_escape_string($conn, $_POST["team_$i"]));

        mysqli_query($conn, "INSERT INTO goal_stats (fixture_id, team, goal_scorer, assist_provider, season_id, status) 
                             VALUES ('$fixture_id','$team','$goal_scorer','$assist_provider' , '$season_id', 'Stats entered' )");
    }
    header("Location: fixture_details.php?id=$fixture_id");
    exit;
}

// Handle update of existing stats
if (isset($_POST['update_stats'])) {
    $stats_id_array = $_POST['stats_id']; // array of stat ids
    foreach($stats_id_array as $id) {
        $goal_scorer = trim(mysqli_real_escape_string($conn, $_POST["goal_scorer_$id"]));
        $assist_provider = trim(mysqli_real_escape_string($conn, $_POST["assist_provider_$id"]));
        $team = trim(mysqli_real_escape_string($conn, $_POST["team_$id"]));

        mysqli_query($conn, "UPDATE goal_stats 
                             SET goal_scorer='$goal_scorer', assist_provider='$assist_provider', team='$team'
                             WHERE id='$id'");
    }
    header("Location: fixture_details.php?id=$fixture_id");
    exit;
}

// Handle deletion of all player stats for this fixture
if (isset($_POST['delete_players'])) {
    mysqli_query($conn, "DELETE FROM goal_stats WHERE fixture_id=$fixture_id");
     // Reset AUTO_INCREMENT to fill deleted IDs
        mysqli_query($conn, "ALTER TABLE goal_stats AUTO_INCREMENT = 1");
    header("Location: fixture_details.php?id=$fixture_id");
    exit;
}

// Fetch existing stats for display
$stats = mysqli_query($conn, "SELECT * FROM goal_stats WHERE fixture_id=$fixture_id");
$existing_stats_count = mysqli_num_rows($stats);

// Total goals
$total_goals = $fixtureData['home_score'] + $fixtureData['away_score'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fixture Details</title>
<link rel="stylesheet" href="styles/create_season.css?v=6">
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 30px;
        background: #f8f9fb;
        color: #222;
    }
    .fixture-header {
        background: #1e3c72;
        color: #fff;
        padding: 18px 20px;
        text-align: center;
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 30px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    }
    h3 {
        color: #1e3c72;
        margin: 20px 10px;
    }
    form {
        background: #ffffff;
        padding: 18px;
        border-radius: 10px;
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
        margin: 25px;
    }
    input[type=text], input[type=number], select {
        padding: 8px 10px;
        margin: 6px 6px 6px 0;
        border-radius: 5px;
        border: 1px solid #bbb;
        width: 220px;
    }
    button {
        padding: 8px 16px;
        margin: 8px 0;
        background: #0073e6;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
        font-size: 14px;
    }
    button:hover {
        background: #005bb5;
    }
    .stat-row {
        margin: 12px;
    }
    .stats-display {
        display: flex;
        justify-content: space-between;
        margin: 20px;
    }
    .team-stats {
        width: 45%;
        background: #f1f4f9;
        padding: 14px;
        border-radius: 10px;
    }
    .team-stats div {
        background: #fff;
        padding: 8px;
        margin-bottom: 6px;
        border-radius: 6px;
        border: 1px solid #ddd;
    }
    .stats-display h4 {
        text-align: center;
        margin-bottom: 10px;
    }
</style>
</head>
<body>

<div class="fixture-header">
    <?php echo htmlspecialchars($fixtureData['home_team'])." ".$fixtureData['home_score']." vs ".$fixtureData['away_score']." ".htmlspecialchars($fixtureData['away_team']); ?>
</div>

<div class="back-button">
    <a href="season.php?id=<?php echo $season_id; ?>#fixture-<?php echo $fixture_id; ?>">← Back to Home</a>
</div>

<h3>Update Fixture Details</h3>
<form method="POST">
    <label>Home Team:</label>
    <input type="text" name="home_team" value="<?php echo htmlspecialchars($fixtureData['home_team']); ?>" required>
    <label>Away Team:</label>
    <input type="text" name="away_team" value="<?php echo htmlspecialchars($fixtureData['away_team']); ?>" required>
    <button type="submit" name="update_fixture">Update Fixture</button>
</form>

<h3>Update Match Result</h3>
<form method="POST">
    <input type="number" name="home_score" value="<?php echo $fixtureData['home_score']; ?>" required>
    <input type="number" name="away_score" value="<?php echo $fixtureData['away_score']; ?>" required>
    <button type="submit" name="update_result">Update Result</button>
</form>

<h3>Goal Scorers & Assist Providers</h3>

<?php if($existing_stats_count > 0): ?>
    <form method="POST">
        <?php 
        mysqli_data_seek($stats, 0);
        while($s = mysqli_fetch_assoc($stats)){
            $id = $s['id'];
        ?>
            <div class="stat-row">
                Goal <?php echo $id; ?>:
                <input type="text" name="goal_scorer_<?php echo $id; ?>" value="<?php echo htmlspecialchars($s['goal_scorer']); ?>">
                <input type="text" name="assist_provider_<?php echo $id; ?>" value="<?php echo htmlspecialchars($s['assist_provider']); ?>">
                <select name="team_<?php echo $id; ?>">
                    <option value="Home" <?php if($s['team']=="Home") echo "selected"; ?>>Home</option>
                    <option value="Away" <?php if($s['team']=="Away") echo "selected"; ?>>Away</option>
                </select>
                <input type="hidden" name="stats_id[]" value="<?php echo $id; ?>">
            </div>
        <?php } ?>
        <button type="submit" name="update_stats">Update Stats</button>
        <button type="submit" name="delete_players" style="background-color: red;">Delete All Players</button>
    </form>
<?php else: ?>
    <form method="POST">
        <input type="hidden" name="total_goals" value="<?php echo $total_goals; ?>">
        <?php for($i=1; $i<=$total_goals; $i++){ ?>
            <div class="stat-row">
                Goal <?php echo $i; ?>:
                <input type="text" name="goal_scorer_<?php echo $i; ?>" placeholder="Goal Scorer">
                <input type="text" name="assist_provider_<?php echo $i; ?>" placeholder="Assist Provider">
                <select name="team_<?php echo $i; ?>">
                    <option value="">Select Team</option>
                    <option value="Home">Home</option>
                    <option value="Away">Away</option>
                </select>
            </div>
        <?php } ?>
        <button type="submit" name="submit_stats">Submit Stats</button>
    </form>
<?php endif; ?>

<h3>Current Stats</h3>
<div class="stats-display">
    <div class="team-stats">
        <h4><?php echo htmlspecialchars($fixtureData['home_team']); ?></h4>
        <?php
        mysqli_data_seek($stats, 0);
        $found = false;
        while($s = mysqli_fetch_assoc($stats)){
            if($s['team'] == "Home"){
                echo "<div><strong>Goal:</strong> ".htmlspecialchars($s['goal_scorer'])." | <strong>Assist:</strong> ".htmlspecialchars($s['assist_provider'])."</div>";
                $found = true;
            }
        }
        if(!$found) echo "<p>No stats yet.</p>";
        ?>
    </div>

    <div class="team-stats">
        <h4><?php echo htmlspecialchars($fixtureData['away_team']); ?></h4>
        <?php
        mysqli_data_seek($stats, 0);
        $found = false;
        while($s = mysqli_fetch_assoc($stats)){
            if($s['team'] == "Away"){
                echo "<div><strong>Goal:</strong> ".htmlspecialchars($s['goal_scorer'])." | <strong>Assist:</strong> ".htmlspecialchars($s['assist_provider'])."</div>";
                $found = true;
            }
        }
        if(!$found) echo "<p>No stats yet.</p>";
        ?>
    </div>
</div>

</body>
</html>
