<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reservation_date']) && isset($_POST['time_slot'])) {
    $reservation_date = trim($_POST['reservation_date']);
    $time_slot = trim($_POST['time_slot']);

    // Validate date format (YYYY-MM-DD)
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $reservation_date)) {
        die("❌ Invalid date format.");
    }

    // Get the current date and the max allowed booking date (2 months ahead)
    $current_date = date("Y-m-d");
    $max_date = date("Y-m-d", strtotime("+2 months"));

    // Prevent checking past dates
    if ($reservation_date < $current_date) {
        die("❌ Cannot check past dates.");
    }

    // Restrict booking within the next 2 months
    if ($reservation_date > $max_date) {
        die("❌ You can only book within the next 2 months.");
    }

    // Query to check existing reservations for the selected slot
    $sql = "SELECT COUNT(*) FROM reservations WHERE reservation_date = ? AND time_slot = ? AND status = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $reservation_date, $time_slot);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    if ($count > 0) {
        echo "❌ Not Available";
    } else {
        echo "✅ Available";
    }
}
?>
