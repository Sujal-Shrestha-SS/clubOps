<?php
session_start();
include 'db.php';

$season_id = $_SESSION['club_id']; // Current season
$club_name = $_SESSION['club_name'];

// --- Fetch Fixtures ---
$sql_fixtures = "
    SELECT id, home_team, away_team, home_score, away_score, status
    FROM fixtures
    WHERE season_id = '$season_id'
    ORDER BY id ASC
";
$result_fixtures = mysqli_query($conn, $sql_fixtures);

$fixtures = [];
if ($result_fixtures && mysqli_num_rows($result_fixtures) > 0) {
    while ($row = mysqli_fetch_assoc($result_fixtures)) {
        // Determine match result from perspective of club
        if ($row['home_team'] === $club_name) {
            $row['result'] = ($row['home_score'] > $row['away_score']) ? 'win' :
                             (($row['home_score'] == $row['away_score']) ? 'draw' : 'loss');
            $row['venue'] = 'home';
        } elseif ($row['away_team'] === $club_name) {
            $row['result'] = ($row['away_score'] > $row['home_score']) ? 'win' :
                             (($row['away_score'] == $row['home_score']) ? 'draw' : 'loss');
            $row['venue'] = 'away';
        } else {
            $row['result'] = '';
            $row['venue'] = '';
        }
        $fixtures[] = $row;
    }
}

// --- Fetch Goal Stats grouped by fixture_id ---
$sql_goal_stats = "
    SELECT fixture_id, goal_scorer, assist_provider, team
    FROM goal_stats
    WHERE fixture_id IN (
        SELECT id FROM fixtures WHERE season_id = '$season_id'
    )
    ORDER BY fixture_id ASC, id ASC
";
$result_goal_stats = mysqli_query($conn, $sql_goal_stats);
$goal_stats = [];
if ($result_goal_stats && mysqli_num_rows($result_goal_stats) > 0) {
    while ($row = mysqli_fetch_assoc($result_goal_stats)) {
        $goal_stats[$row['fixture_id']][] = [
            'scorer' => $row['goal_scorer'],
            'assist' => $row['assist_provider'],
            'team'   => $row['team']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $club_name; ?> - Fixtures</title>
<link rel="stylesheet" href="styles/create_season.css">
<link rel="stylesheet" href="styles/home-sidebar.css">
<style>
body { margin: 20px;  color: white; }
h1 { text-align: center; margin-bottom: 20px; }

.fixture-container { margin: 30px auto; width: 95%; max-width: 1000px; }
.fixture-list { display: flex; flex-direction: column; gap: 25px; }

.fixture {
    padding: 20px;
    border-radius: 10px;
    background: rgba(255,255,255,0.1);
    transition: all 0.3s;
}
.fixture.finished { background: rgba(0,255,0,0.1); }
.fixture.upcoming { background: rgba(255,255,0,0.1); }

.matchday-row {
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    background: rgba(0,0,0,0.2);
    padding: 10px 0;
    margin-bottom: 10px;
    border-radius: 6px;
}

.fixture-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 22px;
    margin-bottom: 15px;
}
.fixture-header .team { flex: 1; text-align: center; font-weight: bold; }
.fixture-header .score { font-size: 24px; font-weight: bold; margin: 0 15px; }

.goals { display: flex; justify-content: space-between; margin-top: 10px; }
.goal-list { flex: 1; font-size: 16px; }
.goal-list.left { text-align: left; padding-right: 30px; }
.goal-list.right { text-align: right; padding-left: 30px; }
.goal { margin-bottom: 5px; }

/* Filter dropdowns */
.filters {
    margin-bottom:20px;
    text-align:center;
}
.filters select {
    padding: 8px 12px;
    margin: 0 10px 10px 0;
    border-radius: 6px;
    border: none;
    font-weight: bold;
    color: #fff;
    background: #1e293b;
    cursor: pointer;
    transition: 0.3s;
}
.filters select:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}
.filters label {
    margin-right: 5px;
    font-weight: bold;
}
</style>
</head>
<body>

<h1><?php echo $club_name; ?> - Fixtures</h1>
<?php include 'backtoindex.php'; ?>

<div class="main-content">
    <?php include 'home_sidebar.php'; ?>
    
    <div class="fixture-container">

        <!-- Filters -->
        <div class="filters">
            <label for="venueFilter">Venue:</label>
            <select id="venueFilter">
                <option value="all">All Matches</option>
                <option value="home">Home</option>
                <option value="away">Away</option>
            </select>

            <label for="resultFilter">Result:</label>
            <select id="resultFilter">
                <option value="all">All Results</option>
                <option value="win">Win</option>
                <option value="draw">Draw</option>
                <option value="loss">Loss</option>
            </select>
        </div>

        <h3>All Fixtures</h3>
        <?php if (!empty($fixtures)): ?>
        <div class="fixture-list">
            <?php $matchday = 1; ?>
            <?php foreach ($fixtures as $fixture): ?>
                <?php
                    $status_class = strtolower($fixture['status']) === 'finished' ? 'finished' : 'upcoming';
                    $fixture_id = $fixture['id'];

                    $home_goals = $away_goals = [];
                    if (!empty($goal_stats[$fixture_id])) {
                        foreach ($goal_stats[$fixture_id] as $goal) {
                            if (isset($goal['team']) && $goal['team'] === "Home") $home_goals[] = $goal;
                            else $away_goals[] = $goal;
                        }
                    }
                ?>
                <div class="fixture <?php echo $status_class; ?>" 
                     data-result="<?php echo $fixture['result']; ?>" 
                     data-venue="<?php echo $fixture['venue']; ?>">
                    
                    <div class="matchday-row">Matchday <?php echo $matchday++; ?></div>

                    <div class="fixture-header">
                        <div class="team"><?php echo htmlspecialchars($fixture['home_team']); ?></div>
                        <div class="score">
                            <?php echo $fixture['home_score'] !== null ? $fixture['home_score'] : ''; ?>
                            V
                            <?php echo $fixture['away_score'] !== null ? $fixture['away_score'] : ''; ?>
                        </div>
                        <div class="team"><?php echo htmlspecialchars($fixture['away_team']); ?></div>
                    </div>

                    <div class="goals">
                        <div class="goal-list left">
                            <?php foreach ($home_goals as $goal): ?>
                                <div class="goal">
                                    <?php echo htmlspecialchars($goal['scorer']); ?>
                                    <?php if (!empty($goal['assist'])) echo " | " . htmlspecialchars($goal['assist']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="goal-list right">
                            <?php foreach ($away_goals as $goal): ?>
                                <div class="goal">
                                    <?php echo htmlspecialchars($goal['scorer']); ?>
                                    <?php if (!empty($goal['assist'])) echo " | " . htmlspecialchars($goal['assist']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p style="text-align:center; margin-top: 20px">No fixtures available.</p>
        <?php endif; ?>
    </div>
</div>

<script>
const venueSelect = document.getElementById('venueFilter');
const resultSelect = document.getElementById('resultFilter');
const fixtures = document.querySelectorAll('.fixture');

function filterFixtures() {
    const venueFilter = venueSelect.value;
    const resultFilter = resultSelect.value;

    fixtures.forEach(fix => {
        const matchVenue = fix.getAttribute('data-venue');
        const matchResult = fix.getAttribute('data-result');

        let show = true;

        if (venueFilter !== 'all' && matchVenue !== venueFilter) show = false;
        if (resultFilter !== 'all' && matchResult !== resultFilter) show = false;

        fix.style.display = show ? 'block' : 'none';
    });
}

venueSelect.addEventListener('change', filterFixtures);
resultSelect.addEventListener('change', filterFixtures);
</script>

</body>
</html>
