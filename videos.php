<?php
session_start();
include 'db.php';
include 'admin_sessioncheck.php'; 

$season_id = $_SESSION['club_id']; // Current season
$clubName = $_SESSION['club_name'];

// Handle video upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['video'])) {
    $title = mysqli_real_escape_string($conn, trim($_POST['title'])); // sanitize input
    $target_dir = "uploads/videos/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

    $video_name = basename($_FILES['video']['name']);
    $target_file = $target_dir . $video_name;

    if (move_uploaded_file($_FILES['video']['tmp_name'], $target_file)) {
        $sql = "INSERT INTO videos (season_id, filename, title) VALUES ($season_id, '$video_name', '$title')";
        mysqli_query($conn, $sql);
        header("Location: " . $_SERVER['PHP_SELF']); // refresh page
        exit;
    } else {
        echo "<div class='error-message'>❌ Error uploading video. File may be too large.</div>";
    }
}

// Handle video deletion
if (isset($_POST['delete_video'])) {
    $video_id = (int)$_POST['video_id'];
    $res = mysqli_query($conn, "SELECT filename FROM videos WHERE id=$video_id");
    if ($row = mysqli_fetch_assoc($res)) {
        $file = "uploads/videos/" . $row['filename'];
        if (file_exists($file)) unlink($file);
    }
    mysqli_query($conn, "DELETE FROM videos WHERE id=$video_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle video title edit
if (isset($_POST['edit_video'])) {
    $video_id = (int)$_POST['video_id'];
    $new_title = mysqli_real_escape_string($conn, trim($_POST['new_title']));
    mysqli_query($conn, "UPDATE videos SET title='$new_title' WHERE id=$video_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch only videos for this season
$result = mysqli_query($conn, "SELECT * FROM videos WHERE season_id = $season_id ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Club Videos</title>
    <link rel="stylesheet" href="styles/create_season.css">
    <link rel="stylesheet" href="styles/sidebar.css">
    <style>
        
        body {
            margin: 30px;
        }

        .error-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #ff4d4d;
            color: white;
            padding: 20px 30px;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 9999;
            text-align: center;
        }

        form input[type="text"] {
            padding: 8px 12px;
            margin-right: 10px;
            border-radius: 6px;
            border: 2px solid #14233f;
            font-size: 14px;
            width: 200px;
        }

        input[type="file"] {
            padding: 8px;
            border: 2px solid #14233f;
            border-radius: 8px;
            background: linear-gradient(135deg, #132665cd, #4b5d7aff);
            font-family: inherit;
            cursor: pointer;
        }

        input[type="file"]::file-selector-button {
            background: #14233f;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        input[type="file"]::file-selector-button:hover {
            background: #1d68f1ff;
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

        .video-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .video-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: linear-gradient(45deg, #412fb3d6, #0a1331ff);
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        .video-title {
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
            color: #fff;
            font-size: 20px;
        }

        .video-actions {
            margin-top: 5px;
            font-size: 14px;
        }

        .video-actions a {
            color: #1d4ed8;
            text-decoration: none;
            margin: 0 5px;
        }

        .video-actions a:hover {
            text-decoration: underline;
        }

        video {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0px 2px 6px rgba(0,0,0,0.3);
        }
    </style>
    
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <h2>Upload Video (<?php echo $clubName; ?>)</h2>
        <?php include 'backtohome.php'; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Video title" required>
            <input type="file" name="video" accept="video/*" required>
            <button type="submit">Upload</button>
        </form>

        <h2><?php echo $clubName; ?> Media</h2>
        <div class="video-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="video-card">
                        <video controls>
                            <source src="uploads/videos/<?php echo $row['filename']; ?>" type="video/mp4">
                            Your browser does not support video playback.
                        </video>
                        <p class="video-title"><?php echo htmlspecialchars($row['title']); ?></p>
                        <div class="video-actions">
                        <!-- Edit title inline -->
                        <form method="POST" style="display:inline;">
                            <input type="text" name="new_title" placeholder="New title" required>
                            <input type="hidden" name="video_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="edit_video">Edit</button>
                        </form>

                        <!-- Delete video -->
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this video?');">
                            <input type="hidden" name="video_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_video">Delete</button>
                        </form>
                    </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No videos uploaded for this season yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
