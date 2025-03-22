<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$availability_message = ''; 
$reservation_date = isset($_POST['reservation_date']) ? $_POST['reservation_date'] : ''; 
$time_slot = isset($_POST['time_slot']) ? $_POST['time_slot'] : ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $status = 'pending';

    if (isset($_POST['check_availability']) || isset($_POST['reserve'])) {
        // Get current date
        $current_date = date("Y-m-d");

        // Prevent booking past dates
        if ($reservation_date < $current_date) {
            $availability_message = "❌ Cannot book a past date.";
        } else {
            // Check if the user has already booked for the selected date and time
            $check_user_sql = "SELECT COUNT(*) FROM reservations WHERE user_id = ? AND reservation_date = ? AND time_slot = ?";
            $stmt = $conn->prepare($check_user_sql);
            $stmt->bind_param("iss", $user_id, $reservation_date, $time_slot);
            $stmt->execute();
            $stmt->bind_result($user_reservation);
            $stmt->fetch();
            $stmt->close();

            if ($user_reservation > 0) {
                $availability_message = "❌ You have already booked this time slot on this date.";
            } else {
                // Check the number of users who have already booked for the same date and time
                $check_slot_sql = "SELECT COUNT(*) FROM reservations WHERE reservation_date = ? AND time_slot = ?";
                $stmt = $conn->prepare($check_slot_sql);
                $stmt->bind_param("ss", $reservation_date, $time_slot);
                $stmt->execute();
                $stmt->bind_result($existing_reservations);
                $stmt->fetch();
                $stmt->close();

                // Check if the time slot is available
                if ($existing_reservations >= 5) {
                    $availability_message = "❌ This time slot is fully booked. Please choose another time.";
                } else {
                    $availability_message = "✅ This time slot is available. You can proceed with the reservation.";

                    // If the user clicks "Reserve" and the slot is available, proceed with reservation
                    if (isset($_POST['reserve'])) {
                        // Prevent booking past dates
                        if ($reservation_date < $current_date) {
                            die("❌ Cannot book a past date.");
                        }

                        // Restrict booking within the next 2 months
                        $max_date = date("Y-m-d", strtotime("+2 months"));
                        if ($reservation_date > $max_date) {
                            die("❌ You can only book within the next 2 months.");
                        }

                        // Insert reservation
                        $insert_sql = "INSERT INTO reservations (user_id, reservation_date, time_slot, status) VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($insert_sql);
                        $stmt->bind_param("isss", $user_id, $reservation_date, $time_slot, $status);

                        if ($stmt->execute()) {
                            echo "<p style='color: green;'>✅ Reservation successful! Redirecting to dashboard...</p>";
                            header("Refresh: 2; URL=dashboard.php"); // Redirect after 2 seconds
                            exit(); // Ensure script execution stops after redirection
                        } else {
                            $availability_message = "❌ Error: " . $stmt->error;
                        }

                        $stmt->close();
                    }
                }
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Reservation</title>
</head>
<body>
    <h2>Make a Reservation</h2>

    <form method="post" action="reserve.php">
        <label for="reservation_date">Date:</label>
        <input type="date" id="reservation_date" name="reservation_date" value="<?= htmlspecialchars($reservation_date) ?>" required><br>

        <label for="time_slot">Time Slot:</label>
        <select id="time_slot" name="time_slot">
            <option value="08:00:00" <?= ($time_slot == "08:00:00") ? 'selected' : '' ?>>08:00 AM - 10:00 AM</option>
            <option value="10:00:00" <?= ($time_slot == "10:00:00") ? 'selected' : '' ?>>10:00 AM - 12:00 PM</option>
            <option value="13:00:00" <?= ($time_slot == "13:00:00") ? 'selected' : '' ?>>01:00 PM - 03:00 PM</option>
            <option value="15:00:00" <?= ($time_slot == "15:00:00") ? 'selected' : '' ?>>03:00 PM - 05:00 PM</option>
        </select><br>

        <button type="submit" name="check_availability">Check Availability</button>
        <?php if ($availability_message == "✅ This time slot is available. You can proceed with the reservation.") { ?>
            <button type="submit" name="reserve">Reserve</button>
        <?php } ?>
    </form>

    <?php if ($availability_message) { echo "<p>$availability_message</p>"; } ?>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
