<?php
session_start();
include 'db.php';

$season_id = $_SESSION['club_id']; // Current season
$clubName = $_SESSION['club_name'];

// Fetch only videos for this season
$result = mysqli_query($conn, "SELECT * FROM videos WHERE season_id = $season_id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $clubName; ?> Media</title>
    <link rel="stylesheet" href="styles/create_season.css">
    <link rel="stylesheet" href="styles/home-sidebar.css">
    <style>
        body {
            margin: 20px;
            
        }

        h1 {
            text-align: center;
        }

        .video-container {
            margin: 50px auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .video-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(135deg, #b58900, #6a0dad);
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .video-card video {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0px 2px 6px rgba(0,0,0,0.2);
        }

        .video-title {
            margin-top: 20px;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            color: #fff
            font-size: 20px;
        }

        p.no-videos {
            text-align: center;
            font-size: 16px;
            color: #fff;
        }
    </style>
</head>
<body>
    <h1><?php echo $clubName; ?> - Media Gallery</h1>
    <?php include 'backtoindex.php'; ?>    

    <div class="main-content">
            <?php include 'home_sidebar.php'; ?>

        <div class="video-container">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="video-card">
                        <video controls>
                            <source src="uploads/videos/<?php echo $row['filename']; ?>" type="video/mp4">
                            Your browser does not support video playback.
                        </video>
                        <p class="video-title"><?php echo htmlspecialchars($row['title']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-videos">No videos available for this season.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
