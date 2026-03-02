<?php
session_start();
include 'db.php';

$season_id = $_SESSION['club_id'] ?? null;
if (!$season_id) {
    die("Season not selected.");
}

// Fetch all players for dropdown
$players = mysqli_query($conn, "SELECT id, name, picture FROM players WHERE season_id='$season_id' ORDER BY name");

$player1 = $player2 = null;
$linkups = []; // fixture_id => [fixture, goals]
$total_linkups = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player1 = (int)$_POST['player1'];
    $player2 = (int)$_POST['player2'];

    if ($player1 && $player2 && $player1 !== $player2) {
        // Fetch names
        $p1 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM players WHERE id='$player1'"));
        $p2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM players WHERE id='$player2'"));

        $player1_name = $p1['name'];
        $player2_name = $p2['name'];

        // Query linkups
        $sql = "
            SELECT g.fixture_id, g.goal_scorer, g.assist_provider, 
                f.home_team, f.away_team, f.home_score, f.away_score
            FROM goal_stats g
            JOIN fixtures f ON g.fixture_id = f.id
            WHERE f.season_id = '$season_id'
            AND (
                    (g.goal_scorer = '$player1_name' AND g.assist_provider = '$player2_name')
                OR (g.goal_scorer = '$player2_name' AND g.assist_provider = '$player1_name')
                )
            ORDER BY f.id
        ";

        $res = mysqli_query($conn, $sql);

        while ($row = mysqli_fetch_assoc($res)) {
            $fixture_id = $row['fixture_id'];

            if (!isset($linkups[$fixture_id])) {
                $linkups[$fixture_id] = [
                    'fixture' => [
                        'home_team' => $row['home_team'],
                        'away_team' => $row['away_team'],
                        'home_score' => $row['home_score'],
                        'away_score' => $row['away_score'],
                    ],
                    'goals' => []
                ];
            }

            // Add each linkup for this fixture
            $linkups[$fixture_id]['goals'][] = [
                'scorer' => $row['goal_scorer'],
                'assist' => $row['assist_provider']
            ];

            $total_linkups++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Player Linkup Stats</title>
<link rel="stylesheet" href="styles/create_season.css">
<link rel="stylesheet" href="styles/home-sidebar.css">
<style>
body { margin:20px; }
h1 { text-align:center; }
form { 
    text-align:center; 
    margin-bottom:30px;
    margin-top: 40px; 
}
.player-select-container {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-top: 20px;
    flex-wrap: wrap;
}
.player-box {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.player-box select {
    padding: 8px 12px;
    border: 1px solid #444;
    border-radius: 6px;
    background: #1e293b;
    color: #fff;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.player-box select:hover {
    background: #334155;
    border-color: #3b82f6;
}
.player-box select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 6px rgba(59, 130, 246, 0.6);
}
.player-box img {
    display: none;
    margin-top: 10px;
    max-height: 120px;
    border-radius: 10px;
    border: 2px solid #3b82f6;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}
button {
    padding: 10px 18px;
    margin-top: 20px;
    border: none;
    border-radius: 6px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}
button:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}
button:active { transform: scale(0.97); }
.fixture-div {
    border:1px solid #ccc;
    padding:15px;
    margin: 40px auto;
    width:70%;
    border-radius:10px;
    background:rgba(255,255,255,0.05);
}
.fixture-div h3 { margin:0; }
.fixture-div ul { margin-top:10px; }




</style>
</head>
<body>

<h1>Player Linkup Stats</h1>
<?php include 'backtoindex.php'; ?>
<div class="main-content">
    <?php include 'home_sidebar.php'; ?>

    <form method="POST">
        <div class="player-select-container">
            <!-- Player 1 -->
            <div class="player-box">
                <label>Select Player 1:</label><br>
                <select name="player1" id="player1" required onchange="showPlayerImage(this, 'player1-img')">
                    <option value="">-- Select Player --</option>
                    <?php 
                    mysqli_data_seek($players, 0);
                    while($row = mysqli_fetch_assoc($players)): ?>
                        <option value="<?php echo $row['id']; ?>" 
                                data-picture="<?php echo $row['picture'] ?? 'uploads/default.png'; ?>"
                                <?php echo ($player1==$row['id'])?'selected':''; ?>>
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <img id="player1-img" src="" alt="Player 1">
            </div>

            <!-- Player 2 -->
            <div class="player-box">
                <label>Select Player 2:</label><br>
                <?php $players2 = mysqli_query($conn, "SELECT id, name, picture FROM players WHERE season_id='$season_id' ORDER BY name"); ?>
                <select name="player2" id="player2" required onchange="showPlayerImage(this, 'player2-img')">
                    <option value="">-- Select Player --</option>
                    <?php while($row = mysqli_fetch_assoc($players2)): ?>
                        <option value="<?php echo $row['id']; ?>" 
                                data-picture="<?php echo $row['picture'] ?? 'uploads/default.png'; ?>"
                                <?php echo ($player2==$row['id'])?'selected':''; ?>>
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <img id="player2-img" src="" alt="Player 2">
            </div>
        </div>

        <button type="submit">Show Linkup</button>
    </form>

    <?php if ($player1 && $player2): ?>
        <h2 style="text-align:center;">
            Total Linkups: <?php echo $total_linkups; ?> <br> Fixtures Involved: <?php echo count($linkups); ?>
        </h2>

        <?php if (!empty($linkups)): ?>
            <?php foreach($linkups as $fixture): ?>
                <div class="fixture-div">
                    <h3>
                        <?php echo $fixture['fixture']['home_team']." ".$fixture['fixture']['home_score']
                                 ." - ".$fixture['fixture']['away_score']." ".$fixture['fixture']['away_team']; ?>
                    </h3>
                    <ul>
                        <?php foreach($fixture['goals'] as $g): ?>
                            <li><?php echo $g['scorer']; ?> (Assist: <?php echo $g['assist']; ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center;">No goals where these two players linked up.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function showPlayerImage(selectElement, imgId) {
    let img = document.getElementById(imgId);
    let option = selectElement.options[selectElement.selectedIndex];
    let picture = option.getAttribute('data-picture');

    if (picture && selectElement.value) {
        img.src = picture;
        img.style.display = "block";
    } else {
        img.style.display = "none";
    }
}

// Auto-load images if players were pre-selected (after form submit)
window.onload = function() {
    showPlayerImage(document.getElementById('player1'), 'player1-img');
    showPlayerImage(document.getElementById('player2'), 'player2-img');
};
</script>

</body>
</html>
