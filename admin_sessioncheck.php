<?php

if(!isset($_SESSION['admin'])){
    echo "<script>
            alert('Login required');
            window.location.href = 'index.php';
          </script>";
    exit();
}

?>