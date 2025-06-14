<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

class SMTPMailer {

    private $mail;
    private $from = APP_EMAIL;
    private $fromName = APP_NAME;
    private $logFile = __DIR__ . '/../../logs/mail.log'; // Path to log file

    public function __construct() {
        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.gmail.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = APP_EMAIL;
        $this->mail->Password = APP_EMAIL_PASSWORD;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 587;
        $this->mail->CharSet = 'UTF-8';

        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Log a message to the mail log file
     * @param string $message
     * @param string $level
     */
    private function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public function sendMail($to, $subject, $body, $isHTML = true) {
        try {
            $this->log("Attempting to send email to: $to with subject: $subject");
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
            $this->log("Email successfully sent to: $to");
            return true;
        } catch (Exception $e) {
            $errorMessage = "Mailer Error: " . $this->mail->ErrorInfo;
            $this->log($errorMessage, 'ERROR');
            return $errorMessage;
        }
    }
    public function addAttachment($filePath, $fileName = '') {
        try {
            $this->log("Adding attachment: $filePath" . ($fileName ? " as $fileName" : ""));
            $this->mail->addAttachment($filePath, $fileName);
            return true;
        } catch (Exception $e) {
            $errorMessage = "Attachment Error: " . $this->mail->ErrorInfo;
            $this->log($errorMessage, 'ERROR');
            return $errorMessage;
        }
    }
    /**
     * Get contents of the mail log file
     * @param int $lines Number of lines to return (0 for all)
     * @return string
     */
    public function getLogContents($lines = 0) {
        if (!file_exists($this->logFile)) {
            return "Log file does not exist.";
        }
        $contents = file_get_contents($this->logFile);
        if ($lines > 0) {
            $logLines = explode(PHP_EOL, $contents);
            $logLines = array_filter($logLines);
            $logLines = array_slice($logLines, -$lines);
            return implode(PHP_EOL, $logLines);
        }
        return $contents;
    }
    /**
     * Clear the mail log file
     * @return bool
     */
    public function clearLogs() {
        if (file_exists($this->logFile)) {
            return file_put_contents($this->logFile, '') !== false;
        }
        return true;
    }
}
?>