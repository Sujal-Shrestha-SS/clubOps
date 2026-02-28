<?php
// Start session and DB connection
session_start();
include 'db.php';
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle new season form submission
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
    .admin-login {
        position: fixed;
        top: 50px;
        right: 30px;
        }

    .admin-login a {
        text-decoration: none;
        background: #14233f; /* same theme color as sidebar */
        color: #fff;
        padding: 10px 15px;
        border-radius: 8px;
        font-weight: bold;
        transition: 0.3s ease;
    }

    .admin-login a:hover {
        background: #1d2d50;
        box-shadow: 0px 0px 10px rgba(20, 35, 63, 0.8);
    }

    @media (max-width: 1450px){

  .admin-login{
      position: relative;
      top: 0;
      right: 0;
      margin-bottom: 20px;
      display: flex;
      justify-content: flex-end;
  }

}

    </style>
</head>
<body>
    <div class="container">

    <!-- Admin Login Button -->
        <div class="admin-login">
            <a href="admin_login.php">Admin Login</a>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1>Club Seasons</h1>

            <!-- Display existing seasons -->
            <div class="season-container">
                <?php
                $result = mysqli_query($conn, "SELECT * FROM seasons");
                while ($row = mysqli_fetch_assoc($result)) {
                     echo "<div class='season-box'>
                            <a href='club.php?id=" . $row['id'] . "' style='text-decoration:none; color:inherit;'>
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
