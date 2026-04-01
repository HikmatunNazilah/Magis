<?php
/**
 * SmtpMailer - Custom SMTP Client for PHP
 * Hand-coded to avoid external dependencies like PHPMailer or Composer.
 */

class SmtpMailer {
    private $host;
    private $port;
    private $user;
    private $pass;
    private $debug = [];

    public function __construct($host, $port, $user, $pass) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
    }

    public function send($to, $subject, $message, $fromName = "System") {
        $errno = 0;
        $errstr = "";
        
        // Use ssl:// for port 465
        $socketHost = ($this->port == 465) ? "ssl://" . $this->host : $this->host;
        
        $socket = fsockopen($socketHost, $this->port, $errno, $errstr, 10);
        if (!$socket) {
            return ["success" => false, "msg" => "Connection failed: $errstr"];
        }

        $this->getResponse($socket); // banner

        $this->sendCommand($socket, "EHLO " . $_SERVER['HTTP_HOST'] ?? 'localhost');
        $this->sendCommand($socket, "AUTH LOGIN");
        $this->sendCommand($socket, base64_encode($this->user));
        $this->sendCommand($socket, base64_encode($this->pass));

        $this->sendCommand($socket, "MAIL FROM: <$this->user>");
        $this->sendCommand($socket, "RCPT TO: <$to>");
        $this->sendCommand($socket, "DATA");

        $headers = "To: $to\r\n";
        $headers .= "From: $fromName <$this->user>\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n\r\n";

        fwrite($socket, $headers . $message . "\r\n.\r\n");
        $finalResponse = $this->getResponse($socket);

        $this->sendCommand($socket, "QUIT");
        fclose($socket);

        if (strpos($finalResponse, '250') === 0 || strpos($finalResponse, 'queued') !== false) {
            return ["success" => true];
        } else {
            return ["success" => false, "msg" => $finalResponse];
        }
    }

    private function sendCommand($socket, $cmd) {
        fwrite($socket, $cmd . "\r\n");
        return $this->getResponse($socket);
    }

    private function getResponse($socket) {
        $response = "";
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (substr($line, 3, 1) == " ") break;
        }
        return $response;
    }
}
?>
