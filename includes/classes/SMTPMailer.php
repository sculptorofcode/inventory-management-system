<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class SMTPMailer {

    private $mail;
    private $from = APP_EMAIL;
    private $fromName = APP_NAME;

    public function __construct() {
        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = APP_EMAIL;
        // $this->mail->Password = 'Dragon567@#';
        $this->mail->Password = APP_EMAIL_PASSWORD;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        $this->mail->CharSet = 'UTF-8';
    }

    public function sendMail($to, $subject, $body, $isHTML = true) {
        try {
            $this->mail->setFrom($this->from, $this->fromName);
            $this->mail->addAddress($to);

            $this->mail->Subject = $subject;
            if ($isHTML) {
                $this->mail->isHTML(true);
                $this->mail->Body = $body;
            } else {
                $this->mail->Body = $body;
            }

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            return "Mailer Error: " . $this->mail->ErrorInfo;
        }
    }
    public function addAttachment($filePath, $fileName = '') {
        try {
            $this->mail->addAttachment($filePath, $fileName);
        } catch (Exception $e) {
            return "Attachment Error: " . $this->mail->ErrorInfo;
        }
    }
}
?>