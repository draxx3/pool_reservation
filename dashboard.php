<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';
$user_id = $_SESSION['user_id'];

// Fetch reservations for the logged-in user
$sql = "SELECT reservation_id, reservation_date, time_slot, status FROM reservations WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Reservations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">

    <h3 class="mb-3">Your Reservations</h3>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['reservation_date']) ?></td>
                    <td><?= htmlspecialchars($row['time_slot']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending') { ?>
                            <form method="post" action="cancel_reservation.php" class="d-inline">
                                <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this reservation?');">Cancel</button>
                            </form>
                        <?php } else { ?>
                            <span class="text-muted">N/A</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="d-flex gap-2">
        <a href="reserve.php" class="btn btn-primary">Reserve</a>
        <a href="logout.php" class="btn btn-secondary">Logout</a>
    </div>

</body>
</html>