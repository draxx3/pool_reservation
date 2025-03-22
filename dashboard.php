<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<?php
include 'db_connect.php';
$user_id = $_SESSION['user_id'];

$sql = "SELECT reservation_id, user_id, reservation_date, time_slot, status FROM reservations WHERE user_id = ?";
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
</head>
<body>

    <h3>Your Reservations</h3>

    <table border="1">
        <tr>
            <th>Date</th>
            <th>Time Slot</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['reservation_date']) ?></td>
                <td><?= htmlspecialchars($row['time_slot']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <?php if ($row['status'] == 'pending') { ?>
                        <form method="post" action="cancel_reservation.php">
                            <input type="hidden" name="reservation_id" value="<?= $row['reservation_id'] ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to cancel this reservation?');">Cancel</button>
                        </form>
                    <?php } else { ?>   
                        N/A
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>

    <form method="get" action="reserve.php">
        <button type="submit">Reserve</button>
    </form>

    <form method="post" action="logout.php">
        <button type="submit">Logout</button>
    </form>



</body>
</html>