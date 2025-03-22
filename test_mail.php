<?php
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';
require 'includes/PHPMailer/Exception.php';
    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = '123demonking@gmail.com';
    $mail->Password = 'mfjm hvjl itdn xojj';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('123demonking@gmail.com', 'Cris Inn');
    $mail->addAddress('123demonking@gmail.com'); // Send test email to yourself
    $mail->Subject = 'Test Email';
    $mail->Body = 'If you receive this email, PHPMailer is working!';

    if ($mail->send()) {
        echo 'Test email sent successfully!';
    } else {
        echo 'Error sending email: ' . $mail->ErrorInfo;
    }
} catch (Exception $e) {
    echo 'Exception error: ' . $e->getMessage();
}
?>
