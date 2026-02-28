<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Simple hardcoded login (you can later connect this to DB)
    if ($username === 'admin' && $password === 'password123') {
        $_SESSION['admin'] = true;
        header("Location: home.php");
        exit;
    } else {
        $error = "Invalid login credentials!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    

    <style>

        :root{
  --bg-a: #0f0c29;
  --bg-b: #302b63;
  --bg-c: #24243e;
  --accent-1: #ff7ab6;
  --accent-2: #6ef0c1;
  --accent-3: #ffd166;
  --glass-1: rgba(255,255,255,0.06);
  --glass-2: rgba(255,255,255,0.08);
  --text-1: #eef2f7;
}

/* ---------- Page background (animated gradient) ---------- */
*{box-sizing:border-box}
html,body{
  height:100%;
  margin:0;
  font-family: 'Poppins', sans-serif;
  color:var(--text-1);
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
  background: linear-gradient(120deg, var(--bg-a), var(--bg-b) 40%, var(--bg-c));
  overflow-y:auto;
  position:relative;
}

        body { font-family: Arial; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background: rgba(45, 132, 148, 0.15); padding: 30px; border-radius: 10px; width: 300px; text-align: center; }
        input { width: 90%; padding: 8px; margin: 10px 0; border-radius: 5px; border: none; }
        button { padding: 8px 16px; border: none; border-radius: 5px; background: #0073e6; color: white; cursor: pointer; }
        button:hover { background: #005bb5; }
        .error { color: #ff4d4d; margin-top: 10px; }

        .back-button {
    margin: 20px;
}

.back-button a {
    text-decoration: none;
    background: #14233f;
    color: #fff;
    padding: 10px 15px;
    border-radius: 8px;
    font-weight: bold;
    transition: 0.3s ease;
}

.back-button a:hover {
    background: #1d2d50;
    box-shadow: 0px 0px 10px rgba(20, 35, 63, 0.8);
}
    </style>
</head>
<body>
    

    <div class="login-box">
        <h2>Admin Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php include 'backtoindex.php'; ?>

    </div>
</body>
</html>
