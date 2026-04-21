<?php

// Setting up the time zone
date_default_timezone_set('Asia/Calcutta');

// Database connection settings
if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1')) {
    // Local Settings (XAMPP)
    $dbhost = 'localhost';
    $dbname = 'jhbewdmy_ssf_in';
    $dbuser = 'root';
    $dbpass = '';
    define("BASE_URL", "http://localhost/araweb/secondsightfoundation-in/");
} else {
    // Live Server Settings
    $dbhost = 'localhost';
    $dbname = 'lyuzmkmy_jhbewdmy_ssf_in';
    $dbuser = 'lyuzmkmy_jhbewdmy_ssf_in';
    $dbpass = 'lyuzmkmy_jhbewdmy_ssf_in';
    
    // Dynamic Base URL detection for Live (handles subfolders)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $dir = dirname($script_name);
    // Remove 'admin' or 'ajax' or 'include' from the end of the directory if the script is inside them
    $dir = preg_replace('/(\/admin|\/ajax|\/include)$/', '', rtrim($dir, '/\\'));
    $base = $protocol . "://" . $host . $dir . "/";
    define("BASE_URL", $base);
}

// Getting Admin url
define("ADMIN_URL", BASE_URL . "admin" . "/");

try {
    $pdo = new PDO("mysql:host={$dbhost};dbname={$dbname}", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Fix for ONLY_FULL_GROUP_BY on live servers
    $pdo->exec("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
} catch (PDOException $exception) {
    die("Connection error (PDO): " . $exception->getMessage());
}

$con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$con) {
    die("Connection error (mysqli): " . mysqli_connect_error());
}
// Fix for ONLY_FULL_GROUP_BY for mysqli
mysqli_query($con, "SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

date_default_timezone_set("Asia/Calcutta");
