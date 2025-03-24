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
        $current_date = date("Y-m-d");

        if ($reservation_date < $current_date) {
            $availability_message = "❌ Cannot book a past date.";
        } else {
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
                $check_slot_sql = "SELECT COUNT(*) FROM reservations WHERE reservation_date = ? AND time_slot = ?";
                $stmt = $conn->prepare($check_slot_sql);
                $stmt->bind_param("ss", $reservation_date, $time_slot);
                $stmt->execute();
                $stmt->bind_result($existing_reservations);
                $stmt->fetch();
                $stmt->close();

                if ($existing_reservations >= 5) {
                    $availability_message = "❌ This time slot is fully booked. Please choose another time.";
                } else {
                    $availability_message = "✅ This time slot is available. You can proceed with the reservation.";

                    if (isset($_POST['reserve'])) {
                        $max_date = date("Y-m-d", strtotime("+2 months"));
                        if ($reservation_date > $max_date) {
                            die("❌ You can only book within the next 2 months.");
                        }

                        $insert_sql = "INSERT INTO reservations (user_id, reservation_date, time_slot, status) VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($insert_sql);
                        $stmt->bind_param("isss", $user_id, $reservation_date, $time_slot, $status);

                        if ($stmt->execute()) {
                            echo "<p class='text-success text-center'>✅ Reservation successful! Redirecting...</p>";
                            header("Refresh: 2; URL=dashboard.php");
                            exit();
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light d-flex justify-content-center align-items-center min-vh-100">

    <div class="card shadow-lg rounded p-4" style="width: 400px;">
        <h2 class="text-center mb-4 text-black">Make a Reservation</h2>

        <form method="post" action="reserve.php">
            <div class="mb-3">
                <label for="reservation_date" class="form-label">Date:</label>
                <input type="date" name="reservation_date" id="reservation_date" value="<?= htmlspecialchars($reservation_date) ?>" required class="form-control" />
            </div>

            <div class="mb-3">
                <label for="time_slot" class="form-label">Time Slot:</label>
                <select name="time_slot" id="time_slot" class="form-select">
                    <option value="08:00:00" <?= ($time_slot == "08:00:00") ? 'selected' : '' ?>>08:00 AM - 10:00 AM</option>
                    <option value="10:00:00" <?= ($time_slot == "10:00:00") ? 'selected' : '' ?>>10:00 AM - 12:00 PM</option>
                    <option value="13:00:00" <?= ($time_slot == "13:00:00") ? 'selected' : '' ?>>01:00 PM - 03:00 PM</option>
                    <option value="15:00:00" <?= ($time_slot == "15:00:00") ? 'selected' : '' ?>>03:00 PM - 05:00 PM</option>
                </select>
            </div>

            <button type="submit" name="check_availability" class="btn btn-primary w-100">Check Availability</button>

            <?php if ($availability_message == "✅ This time slot is available. You can proceed with the reservation.") { ?>
                <button type="submit" name="reserve" class="btn btn-success w-100 mt-3">Reserve</button>
            <?php } ?>
        </form>

        <?php if ($availability_message) { ?>
            <p class="text-center mt-3 text-muted"><?= $availability_message ?></p>
        <?php } ?>

        <a href="dashboard.php" class="btn btn-secondary w-100 mt-3">Back</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
