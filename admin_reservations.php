<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}
include 'db_connect.php';
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';
require 'includes/PHPMailer/Exception.php';
    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Function to send email
function sendEmailNotification($email, $status, $date, $time) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Change this if using another email provider
        $mail->SMTPAuth = true;
        $mail->Username = '123demonking@gmail.com';  // Replace with your email
        $mail->Password = 'mfjm hvjl itdn xojj';  // Replace with your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('123demonking@gmail.com', 'Luke and George Appartelle');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Reservation Status Update";
        $mail->Body = "<p>Your reservation for <b>$date</b> at <b>$time</b> has been <b>$status</b>.</p>";

        $mail->send();
    } catch (Exception $e) {
        echo "Email could not be sent. Error: {$mail->ErrorInfo}";
    }
}

// Handle approval or rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reservation_id = $_POST['reservation_id'];
    $action = $_POST['action'];

    if ($action == "approve") {
        $status = "approved";
    } elseif ($action == "reject") {
        $status = "rejected";
    }

    // Get user email and reservation details
    $sql = "SELECT users.email, reservations.reservation_date, reservations.time_slot 
            FROM reservations 
            JOIN users ON reservations.user_id = users.user_id 
            WHERE reservations.reservation_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $stmt->bind_result($email, $reservation_date, $time_slot);
    $stmt->fetch();
    $stmt->close();

    // Update reservation status
    $sql = "UPDATE reservations SET status = ? WHERE reservation_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $reservation_id);

    if ($stmt->execute()) {
        sendEmailNotification($email, $status, $reservation_date, $time_slot);
        echo "Reservation $status and email sent!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Reservations</title>
</head>
<body>
    <h2>Manage Reservations</h2>
    <table border="1">
        <tr>
            <th>User</th>
            <th>Email</th>
            <th>Date</th>
            <th>Time Slot</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php
        $sql = "SELECT reservations.reservation_id, users.name, users.email, reservations.reservation_date, reservations.time_slot, reservations.status
                FROM reservations
                JOIN users ON reservations.user_id = users.user_id
                WHERE reservations.status = 'pending'";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['reservation_date']}</td>
                    <td>{$row['time_slot']}</td>
                    <td>{$row['status']}</td>
                    <td>
                        <form method='post' style='display:inline;'>
                            <input type='hidden' name='reservation_id' value='{$row['reservation_id']}'>
                            <button type='submit' name='action' value='approve'>Approve</button>
                            <button type='submit' name='action' value='reject'>Reject</button>
                        </form>
                    </td>
                  </tr>";
        }
        ?>
    </table>
    <br>
    <a href="admin_dashboard.php">Back to Dashboard</a>
</body>
</html>
