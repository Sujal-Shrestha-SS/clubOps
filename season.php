<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) die("Season ID not provided.");
$season_id = (int)$_GET['id'];
include 'admin_sessioncheck.php';

// Fetch season info
$season = mysqli_query($conn, "SELECT * FROM seasons WHERE id=$season_id");
$seasonData = mysqli_fetch_assoc($season);
if (!$seasonData) die("Season not found.");

$_SESSION['club_id'] = $season_id;
$_SESSION['club_name'] = $seasonData['club'];

// Handle fixture creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_fixture'])) {
    $home = trim(mysqli_real_escape_string($conn, $_POST['home_team']));
    $away = trim(mysqli_real_escape_string($conn, $_POST['away_team']));

    mysqli_query($conn, "INSERT INTO fixtures (season_id, home_team, away_team) 
                         VALUES ('$season_id','$home','$away')");
    header("Location: season.php?id=$season_id");
    exit;
}

// Handle result submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['score_submit'])) {
    $fixture_id = (int)$_POST['fixture_id'];
    $home_score = (int)$_POST['home_score'];
    $away_score = (int)$_POST['away_score'];

    mysqli_query($conn, "UPDATE fixtures 
                         SET home_score='$home_score', away_score='$away_score', status='Match Completed' 
                         WHERE id='$fixture_id'");
    header("Location: season.php?id=$season_id");
    exit;
}

// Handle fixture deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_fixture'])) {
    $fixture_id = (int)$_POST['delete_fixture_id'];
    mysqli_query($conn, "DELETE FROM fixtures WHERE id='$fixture_id' AND season_id='$season_id'");
    mysqli_query($conn, "ALTER TABLE fixtures AUTO_INCREMENT = 1");
    header("Location: season.php?id=$season_id");
    exit;
}

// Fetch existing fixtures
$fixtures = mysqli_query($conn, "SELECT * FROM fixtures WHERE season_id=$season_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($seasonData['title']); ?></title>
<link rel="stylesheet" href="styles/create_season.css">
<link rel="stylesheet" href="styles/sidebar.css">
<link rel="stylesheet" href="styles/season.css">

</style>
<script>
function toggleForm(id){
    const f = document.getElementById(id);
    f.style.display = f.style.display === 'block' ? 'none' : 'block';
}
function filterFixtures() {
    const filter = document.getElementById("resultFilter").value;
    const fixtures = document.querySelectorAll(".fixture-box");
    fixtures.forEach(fixture => {
        const outcome = fixture.getAttribute("data-outcome");
        fixture.style.display = (filter === "All" || outcome === filter) ? "block" : "none";
    });
}
</script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">

<h1><?php echo htmlspecialchars($seasonData['title']); ?></h1>

<!-- Create Fixture -->
<button onclick="toggleForm('fixtureForm')">+ Create Fixture</button>
<div id="fixtureForm" class="create-fixture-form">
    <form method="POST" action="season.php?id=<?php echo $season_id; ?>">
        <input type="text" name="home_team" placeholder="Home Team" required>
        <input type="text" name="away_team" placeholder="Away Team" required>
        <button type="submit" name="create_fixture">Add Fixture</button>
    </form>
</div>
<?php include 'backtohome.php'; ?>

<h2>Fixtures</h2>

<!-- Filter Dropdown -->
<div class="filter-box">
    <label for="resultFilter"><b>Filter:</b></label>
    <select id="resultFilter" onchange="filterFixtures()">
        <option value="All">All</option>
        <option value="Win">Wins</option>
        <option value="Draw">Draws</option>
        <option value="Loss">Losses</option>
    </select>
</div>

<?php if (mysqli_num_rows($fixtures) === 0) { ?>
    <p style="text-align:center; font-size:18px; color:#fff; margin-top:20px;">No fixture available</p>
<?php } else { 
    $matchday = 1;
    while($row = mysqli_fetch_assoc($fixtures)) { 
        $outcome = "Pending";
        if ($row['status'] === 'Match Completed') {
            $club = $_SESSION['club_name'];
            if ($row['home_team'] === $club || $row['away_team'] === $club) {
                if ($row['home_score'] == $row['away_score']) {
                    $outcome = "Draw";
                } else if (
                    ($row['home_team'] === $club && $row['home_score'] > $row['away_score']) ||
                    ($row['away_team'] === $club && $row['away_score'] > $row['home_score'])
                ) {
                    $outcome = "Win";
                } else {
                    $outcome = "Loss";
                }
            }
        }

        // ✅ Tick Logic
        $stats_check = mysqli_query($conn, "
            SELECT COUNT(*) as total_goals,
                   SUM(CASE WHEN status='Stats entered' THEN 1 ELSE 0 END) as entered_stats
            FROM goal_stats 
            WHERE fixture_id=".$row['id']
        );
        $statsData = mysqli_fetch_assoc($stats_check);
        $showTick = false;
        // Case 1: All stats entered
        if ($statsData['total_goals'] > 0 && $statsData['total_goals'] == $statsData['entered_stats']) {
            $showTick = true;
        }
     
?>
    <div class="fixture-box" id="fixture-<?php echo $row['id']; ?>" data-outcome="<?php echo $outcome; ?>">
        <?php if($showTick) echo '<span class="tick">&#10003;</span>'; ?>
        <div>
            <?php echo "Matchday " . $matchday++ . "<br>"; ?>
            <?php 
            if($row['status']==='Match Completed') {
                echo htmlspecialchars($row['home_team'])." ".$row['home_score']." vs ".$row['away_score']." ".htmlspecialchars($row['away_team']);
            } else {
                echo htmlspecialchars($row['home_team'])." vs ".htmlspecialchars($row['away_team']);
            }
            ?>
        </div>
        
        <div class="fixture-status"><?php echo $row['status']; ?></div>

        <?php if($row['status'] !== 'Match Completed'){ ?>
            <button onclick="toggleForm('resultForm-<?php echo $row['id']; ?>')">Insert Result</button>
            <div id="resultForm-<?php echo $row['id']; ?>" class="result-form">
                <form method="POST" action="">
                    <input type="hidden" name="fixture_id" value="<?php echo $row['id']; ?>">
                    <input type="number" name="home_score" placeholder="Home" required>
                    <input type="number" name="away_score" placeholder="Away" required>
                    <button type="submit" name="score_submit">Submit</button>
                </form>
            </div>
        <?php } else { ?>
            <div class="details-btn">
                <a href="fixture_details.php?id=<?php echo $row['id']; ?>">
                    <button type="button">Details</button>
                </a>
                <form method="POST" action="">
                    <input type="hidden" name="delete_fixture_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="delete_fixture" class="delete-btn">Delete</button>
                </form>
            </div>
        <?php } ?>
    </div>
<?php } } ?>
</div>
</body>
</html>
