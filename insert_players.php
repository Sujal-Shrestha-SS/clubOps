<?php
session_start();
include 'db.php';
$season_id = $_SESSION['club_id'];
include 'admin_sessioncheck.php';

$target_dir = "uploads/"; 
if(!is_dir($target_dir)){
    mkdir($target_dir, 0755, true);
}

// ------------------- HANDLE ADD / UPDATE -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_update') {

    $name = mysqli_real_escape_string($conn, trim($_POST['player_name']));
    $jersey = mysqli_real_escape_string($conn, trim($_POST['jersey_no']));
    $nationality = mysqli_real_escape_string($conn, trim($_POST['nationality']));
    $category = mysqli_real_escape_string($conn, trim($_POST['category']));
    $position = mysqli_real_escape_string($conn, trim($_POST['position']));

    // Handle images
    $player_file = ""; $flag_file = "";
    if(isset($_FILES['player_image']) && $_FILES['player_image']['error'] == 0){
        $filename = time() . "_" . basename($_FILES['player_image']['name']);
        $player_file = $target_dir . $filename;
        move_uploaded_file($_FILES['player_image']['tmp_name'], $player_file);
    }
    if(isset($_FILES['flag_image']) && $_FILES['flag_image']['error'] == 0){
        $flagname = time() . "_" . basename($_FILES['flag_image']['name']);
        $flag_file = $target_dir . $flagname;
        move_uploaded_file($_FILES['flag_image']['tmp_name'], $flag_file);
    }

    if(!empty($_POST['player_id'])){
        $id = intval($_POST['player_id']);
        $update_sql = "UPDATE players SET 
                        name='$name', jersey_no='$jersey', nationality='$nationality',
                        category='$category', position='$position'";
        if($player_file != "") $update_sql .= ", picture='$player_file'";
        if($flag_file != "") $update_sql .= ", flag_picture='$flag_file'";
        $update_sql .= " WHERE id=$id";
        mysqli_query($conn, $update_sql);
    } else {
        mysqli_query($conn, "INSERT INTO players 
            (name, jersey_no, nationality, category, position, picture, flag_picture, season_id) 
            VALUES ('$name','$jersey','$nationality','$category','$position','$player_file','$flag_file', '$season_id')");
    }

    header("Location: insert_players.php?id=$season_id");
    exit;
}

// ------------------- HANDLE DELETE VIA AJAX -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $player_id = intval($_POST['player_id']);
    $result = mysqli_query($conn, "SELECT picture, flag_picture FROM players WHERE id=$player_id");
    if($result && mysqli_num_rows($result) > 0){
        $player_image = mysqli_fetch_assoc($result);
        if(!empty($player_image['picture']) && file_exists($player_image['picture'])){
            unlink($player_image['picture']);
        }
        if(!empty($player_image['flag_picture']) && file_exists($player_image['flag_picture'])){
            unlink($player_image['flag_picture']);
        }
        mysqli_query($conn, "DELETE FROM players WHERE id=$player_id");
         // Reset AUTO_INCREMENT to fill deleted IDs
        mysqli_query($conn, "ALTER TABLE players AUTO_INCREMENT = 1");
        echo json_encode(['status'=>'success']);
    } else {
        echo json_encode(['status'=>'error']);
    }
    exit;
}

// ------------------- FETCH PLAYERS -------------------
$players_result = mysqli_query($conn, "SELECT * FROM players WHERE season_id = $season_id ORDER BY id ASC");
$players = mysqli_fetch_all($players_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Insert Players</title>

<link rel="stylesheet" href="styles/create_season.css">
<link rel="stylesheet" href="styles/sidebar.css">

<style>
body {margin: 30px;}
button {padding:6px 12px;margin:5px; background:#0073e6; color:white; border:none; border-radius:4px; cursor:pointer;}
button:hover {background:#005bb5;}

/* Modal styles */
.modal {display:none; position:fixed; z-index:10000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color: rgba(0,0,0,0.5);}
.modal-content {background: linear-gradient(135deg,#103054ff,#9B59B6); margin:5% auto; padding:20px; border-radius:6px; width:400px; max-height:90%; overflow-y:auto; position:relative;}
.close {position:absolute; top:10px; right:15px; font-size:22px; cursor:pointer;}
.modal-content button[type="submit"] {display:block; margin:10px auto 0 auto; padding:6px 12px; background-color:#0073e6; color:white; border:none; border-radius:4px; cursor:pointer;}
.modal-content button[type="submit"]:hover {background-color:#005bb5;}

/* Inputs */
input[type=text], input[type=number], input[type=file], select {width:100%; padding:5px; margin:8px 0; border:1px solid #aaa; border-radius:4px;}

/* Player grid */
.players-container {display:flex; flex-wrap:wrap; gap:15px; margin-top:10px;}
.player-card {border:1px solid #ccc; padding:10px; border-radius:6px; display:flex; flex-direction:column; align-items:center; width:170px; text-align:center; background:none;}
.player-card img {width:80px; height:80px; object-fit:cover; object-position:top; border-radius:40%; margin-bottom:8px;}
.flag {width:60px; height:35px; object-fit:cover; border-radius:4px; margin-top:6px;}
</style>

<script>
// Open Add Modal
function openModal() {
    document.getElementById('playerModal').style.display='block';
    document.getElementById('playerForm').reset();
    document.getElementById('edit_player_id').value = '';
    document.querySelector('h3.modal-title').innerText = "Add New Player";
}

// Open Edit Modal
function openEditModal(playerId) {
    const players = <?php echo json_encode($players); ?>;
    const player = players.find(p => p.id == playerId);
    if (!player) return;

    document.getElementById('playerModal').style.display='block';
    document.getElementById('edit_player_id').value = player.id;
    document.querySelector('input[name="player_name"]').value = player.name;
    document.querySelector('input[name="jersey_no"]').value = player.jersey_no;
    document.querySelector('input[name="nationality"]').value = player.nationality;
    document.querySelector('select[name="category"]').value = player.category;
    document.querySelector('select[name="position"]').value = player.position;
    document.querySelector('h3.modal-title').innerText = "Edit Player";
}

// Close Modal
function closeModal() { document.getElementById('playerModal').style.display='none'; }

// Delete player with AJAX
function deletePlayer(playerId, element) {
    if(!confirm("Are you sure you want to delete this player?")) return;
    
    const formData = new FormData();
    formData.append('action','delete');
    formData.append('player_id', playerId);

    fetch('insert_players.php', {
        method:'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success'){
            // Remove div instantly
            element.closest('.player-card').remove();
        } else {
            alert("Error deleting player!");
        }
    })
    .catch(err => console.error(err));
}
</script>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
<h1>Players</h1>

<!-- Add Player Button -->
<button onclick="openModal()">+ Add Player</button>
<?php include 'backtohome.php'; ?>

<!-- Display existing players -->
<h2>Existing Players</h2>
<div class="players-container">
<?php if (!empty($players)): ?>
    <?php foreach ($players as $p): ?>
        <div class="player-card">
            <img src="<?php echo !empty($p['picture']) ? htmlspecialchars($p['picture']) : 'uploads/default.png'; ?>" alt="Player">
            <div>
                <strong><?php echo htmlspecialchars($p['name']); ?></strong><br>
                Jersey No: <?php echo $p['jersey_no']; ?><br>
                <?php echo htmlspecialchars($p['nationality']); ?><br>
                <em><?php echo htmlspecialchars($p['category']); ?> - <?php echo htmlspecialchars($p['position']); ?></em>
                <?php if (!empty($p['flag_picture'])): ?>
                    <br><img src="<?php echo htmlspecialchars($p['flag_picture']); ?>" class="flag" alt="Flag">
                <?php endif; ?>
            </div>
            <div style="margin-top:6px;">
                <button style="background:#f4a261;" onclick="openEditModal(<?php echo $p['id']; ?>)">Edit</button>
                <button type="button" style="background:#e63946;" onclick="deletePlayer(<?php echo $p['id']; ?>, this)">Delete</button>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="text-align:center; font-size:18px; color:#fff; margin-top:20px; width:100%;">No players available</p>
<?php endif; ?>
</div>
</div>

<!-- Modal for Add/Edit Player -->
<div id="playerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 class="modal-title">Add New Player</h3>
        <form id="playerForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_update">
            <input type="hidden" name="player_id" id="edit_player_id">
            <input type="text" name="player_name" placeholder="Player Name" required>
            <input type="number" name="jersey_no" placeholder="Jersey Number" required>
            <input type="text" name="nationality" placeholder="Nationality" required>
            
            <label>Category:</label>
            <select name="category" required>
                <option value="">--Select Category--</option>
                <option value="Goalkeeper">Goalkeeper</option>
                <option value="Defender">Defender</option>
                <option value="Midfielder">Midfielder</option>
                <option value="Forward">Forward</option>
            </select>

            <label>Position:</label>
            <select name="position" required>
                <option value="">--Select Position--</option>
                <option value="GK">GK</option>
                <option value="CB">CB</option>
                <option value="LB">LB</option>
                <option value="RB">RB</option>
                <option value="CM">CM</option>
                <option value="CAM">CAM</option>
                <option value="RW">RW</option>
                <option value="LW">LW</option>
                <option value="ST">ST</option>
            </select>

            <label>Nationality Flag:</label>
            <input type="file" name="flag_image" accept="image/*">
            <label>Player Profile:</label>
            <input type="file" name="player_image" accept="image/*">
            
            <button type="submit">Submit</button>
        </form>
    </div>
</div>

</body>
</html>
