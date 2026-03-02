<?php
session_start();
include 'db.php';

$season_id = $_SESSION['club_id'] ?? null;
if (!$season_id) {
    die("Season not selected.");
}

// Fetch season/club info
$season = mysqli_query($conn, "SELECT * FROM seasons WHERE id='$season_id'");
$seasonData = mysqli_fetch_assoc($season);
if (!$seasonData) die("Season not found.");

$club_name = $seasonData['club'];

// Overall metrics
$wins = $draws = $losses = $goals_scored = $goals_conceded = 0;

// Home stats
$home_wins = $home_draws = $home_losses = 0;
$home_goals_scored = $home_goals_conceded = 0;

// Away stats
$away_wins = $away_draws = $away_losses = 0;
$away_goals_scored = $away_goals_conceded = 0;

// Fetch all completed fixtures for this club
$sql = "SELECT * FROM fixtures 
        WHERE (home_team='$club_name' OR away_team='$club_name') 
        AND status='Match Completed'
        AND season_id=$season_id";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $home = $row['home_team'];
        $away = $row['away_team'];
        $home_score = (int)$row['home_score'];
        $away_score = (int)$row['away_score'];

        if ($home === $club_name) {
            // Goals
            $goals_scored += $home_score;
            $goals_conceded += $away_score;
            $home_goals_scored += $home_score;
            $home_goals_conceded += $away_score;

            // Result
            if ($home_score > $away_score) {
                $wins++; $home_wins++;
            } elseif ($home_score == $away_score) {
                $draws++; $home_draws++;
            } else {
                $losses++; $home_losses++;
            }
        } else {
            // Goals
            $goals_scored += $away_score;
            $goals_conceded += $home_score;
            $away_goals_scored += $away_score;
            $away_goals_conceded += $home_score;

            // Result
            if ($away_score > $home_score) {
                $wins++; $away_wins++;
            } elseif ($away_score == $home_score) {
                $draws++; $away_draws++;
            } else {
                $losses++; $away_losses++;
            }
        }
    }
}

// Overall
$goal_difference = $goals_scored - $goals_conceded;
$points = ($wins * 3) + $draws;

// Home
$home_goal_difference = $home_goals_scored - $home_goals_conceded;
$home_points = ($home_wins * 3) + $home_draws;

// Away
$away_goal_difference = $away_goals_scored - $away_goals_conceded;
$away_points = ($away_wins * 3) + $away_draws;

// Position from seasons table
$position = $seasonData['position'] ?? '-';
$club = $club_name ?? '-';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($club_name); ?> - Club Performance</title>
<link rel="stylesheet" href="styles/create_season.css">
<link rel="stylesheet" href="styles/home-sidebar.css">
<style>
body {
    margin: 20px;
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

.performance-table {
    width: 90%;
    margin: 0 auto;
    border-collapse: collapse;
    text-align: center;
    margin-top: 40px;
}

.performance-table th, .performance-table td {
    border: 1px solid white;
    padding: 12px;
}

.performance-table th {
    background: rgba(255,255,255,0.2);
}

.performance-table tr:nth-child(even) {
    background: rgba(255,255,255,0.05);
}

.performance-table tr:nth-child(odd) {
    background: rgba(255,255,255,0.12);
}
</style>
</head>
<body>

<h1><?php echo htmlspecialchars($club_name); ?> - Club Performance</h1>
<?php include 'backtoindex.php'; ?>

<div class="main-content">
    <?php include 'home_sidebar.php'; ?>

    <!-- Overall Stats -->
    <table class="performance-table">
        <tr>
            <th>Club</th>
            <th>Position</th>
            <th>Wins</th>
            <th>Draws</th>
            <th>Losses</th>
            <th>Goals Scored</th>
            <th>Goals Conceded</th>
            <th>Goal Difference</th>
            <th>Points</th>
        </tr>
        <tr>
            <td><?php echo $club; ?></td>
            <td><?php echo $position; ?></td>
            <td><?php echo $wins; ?></td>
            <td><?php echo $draws; ?></td>
            <td><?php echo $losses; ?></td>
            <td><?php echo $goals_scored; ?></td>
            <td><?php echo $goals_conceded; ?></td>
            <td><?php echo $goal_difference; ?></td>
            <td><?php echo $points; ?></td>
        </tr>
    </table>

    <!-- Home/Away Breakdown -->
    <table class="performance-table">
        <tr>
            <th></th>
            <th>Wins</th>
            <th>Draws</th>
            <th>Losses</th>
            <th>Goals Scored</th>
            <th>Goals Conceded</th>
            <th>Goal Difference</th>
            <th>Points</th>
        </tr>
        <tr>
            <td>Home</td>
            <td><?php echo $home_wins; ?></td>
            <td><?php echo $home_draws; ?></td>
            <td><?php echo $home_losses; ?></td>
            <td><?php echo $home_goals_scored; ?></td>
            <td><?php echo $home_goals_conceded; ?></td>
            <td><?php echo $home_goal_difference; ?></td>
            <td><?php echo $home_points; ?></td>
        </tr>
        <tr>
            <td>Away</td>
            <td><?php echo $away_wins; ?></td>
            <td><?php echo $away_draws; ?></td>
            <td><?php echo $away_losses; ?></td>
            <td><?php echo $away_goals_scored; ?></td>
            <td><?php echo $away_goals_conceded; ?></td>
            <td><?php echo $away_goal_difference; ?></td>
            <td><?php echo $away_points; ?></td>
        </tr>
    </table>
</div>
</body>
</html>
