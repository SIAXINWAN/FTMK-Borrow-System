<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';


function sendNotification($toEmail, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ftmkborrowsystem@gmail.com';
        $mail->Password = 'nmhf ehts yllt evep';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('ftmkborrowsystem@gmail.com', 'FTMK Borrow System');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
