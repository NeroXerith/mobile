<?php
include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['recipient_email']) && !empty($_POST['recipient_email'])) {
  $recipient_email = $_POST['recipient_email'];

  require_once "phpmailer/vendor/autoload.php";
  $mail = new PHPMailer(true); // Set to true to enable exceptions

  try {
    $conn = new mysqli($server, $user, $pass, $dbname);

    if ($conn->connect_error) {
      throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Check if the email exists in the database
    $checkEmailSql = "SELECT full_name FROM tbl_users WHERE user_email = ?";
    $checkEmailStmt = $conn->prepare($checkEmailSql);
    $checkEmailStmt->bind_param("s", $recipient_email);
    $checkEmailStmt->execute();
    $checkEmailResult = $checkEmailStmt->get_result();

    if ($checkEmailResult->num_rows > 0) {
      
      // Generate a random password of 8 characters
      $newPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

      // Hash the new password using bcrypt
      $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

      // Update the user's password in the database
      $updateSql = "UPDATE tbl_users SET user_password = ? WHERE user_email = ?";
      $updateStmt = $conn->prepare($updateSql);
      $updateStmt->bind_param("ss", $hashedPassword, $recipient_email);
      $updateStmt->execute();

      if ($updateStmt->affected_rows > 0) {
        while ($row = $checkEmailResult->fetch_assoc()) {
          $name = $row['full_name'];

          // Construct the email body
          $mailBody = "<html><body>";
          $mailBody .= "<p>Dear $name,</p>";
          $mailBody .= "<p>Your password has been reset. Your new password is: $newPassword</p>";
          $mailBody .= "<p>For security reasons, please change this password after login.</p>";
          $mailBody .= "<p>Best regards,<br>Rent-On Support Team</p>";
          $mailBody .= "</body></html>";

          // Set up PHPMailer configurations
          $mail->isSMTP();
          $mail->Host = "smtp-relay.sendinblue.com";
          $mail->SMTPAuth = true;
          $mail->Username = "astar8820@gmail.com";
          $mail->Password = "91tFyTdpaKvGjDZs";
          $mail->SMTPSecure = "tls";
          $mail->Port = 587;
          $mail->setFrom("rentonsupport@gmail.com", "Rent-On Support Team");
          $mail->addAddress($recipient_email, $name);
          $mail->isHTML(true);
          $mail->Subject = "Password Reset";
          $mail->Body = $mailBody;

          // Send the email
          if (!$mail->send()) {
            throw new Exception("Mailer Error: " . $mail->ErrorInfo);
          } else {
            unset($recipient_email);
            echo "Password sent, Check your Email!!";
            exit();
          }
        }
      } else {
        echo "Failed to update password.";
      }
    } else {
      echo "No data found for the provided email";
    }

    $conn->close();
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage();
  }
} else {
  echo "Recipient email cannot be empty.";
}
?>