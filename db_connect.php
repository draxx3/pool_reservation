<?php
$host = "localhost";  
$user = "root"; 
$pass = "";     
$dbname = "pool_reservation_db"; 
    
// Create a connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully!";
}
?>
