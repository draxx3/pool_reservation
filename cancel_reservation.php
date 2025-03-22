<?php
session_start();
include 'db_connect.php';

require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';
require 'includes/PHPMailer/Exception.php';
    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reservation_id'])) {
    $reservation_id = $_POST['reservation_id'];
    $user_id = $_SESSION['user_id'];

    // Get reservation details
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

    // Update the reservation status to "cancelled"
    $sql = "UPDATE reservations SET status = 'cancelled' WHERE reservation_id = ? AND user_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $reservation_id, $user_id);

    if ($stmt->execute()) {
        // Send email notification
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = '123demonking@gmail.com';
            $mail->Password = 'mfjm hvjl itdn xojj';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('123demonking@gmail.com', 'Luke and George Appartelle');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reservation Cancelled';
            $mail->Body = "Your reservation on $reservation_date at $time_slot has been cancelled.";

            $mail->send();
            $_SESSION['message'] = "Reservation cancelled successfully. Email notification sent.";
        } catch (Exception $e) {
            $_SESSION['message'] = "Reservation cancelled, but email could not be sent. Error: {$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['message'] = "Failed to cancel reservation.";
    }

    header("Location: dashboard.php");
    exit();
}
?>
