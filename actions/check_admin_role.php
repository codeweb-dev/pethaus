<?php
if (!isset($_SESSION['type']) || $_SESSION['type'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}
