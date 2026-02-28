<?php
// Start session and DB connection
session_start();
include 'db.php';
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


if(!isset($_SESSION['admin'])){
    echo "<script>
            alert('Login required');
            window.location.href = 'index.php';
          </script>";
    exit();
}


//Handle new season form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['season_title'])) {
    $title = mysqli_real_escape_string($conn, $_POST['season_title']);
    $sql = "INSERT INTO seasons (title) VALUES ('$title')";
    mysqli_query($conn, $sql);
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Football System</title>
    <link rel="stylesheet" href="styles/create_season.css">
    <style>
        .logout-button {
            position: absolute;
            top: 50px;
            right: 50px;
            z-index: 1001; /* ensure it’s above main content */
        }

        .logout-button a {
            text-decoration: none;
            background: #e63946; /* red color for logout */
            color: #fff;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .logout-button a:hover {
            background: #c0392b;
            box-shadow: 0 0 10px rgba(0,0,0,0.4);
        }

    </style>
</head>
<body>
    <div class="container">


    <div class="logout-button">
    <a href="logout.php">Logout</a>
</div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Club Seasons</h1>

            <!-- Button to open form -->
            <button onclick="document.getElementById('createForm').style.display='block'">
                + Create Season
            </button>

            <!-- Hidden form -->
            <div id="createForm" style="display:none; margin-top:10px;">
                <form method="POST" action="">
                    <input type="text" name="season_title" placeholder="Enter season title" required>
                    <button type="submit">Create</button>
                </form>
            </div>

            <!-- Display existing seasons -->
            <div class="season-container">
                <?php
                $result = mysqli_query($conn, "SELECT * FROM seasons");
                while ($row = mysqli_fetch_assoc($result)) {
                     echo "<div class='season-box'>
                            <a href='season.php?id=" . $row['id'] . "' style='text-decoration:none; color:inherit;'>
                                " . htmlspecialchars($row['title']) . "
                            </a>
                          </div>";
                }
                ?>

            </div>
        </div>
    </div>
</body>
</html>
