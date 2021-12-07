<?php
class Mailer {
	private $protocol;
	private $smtp_hostname;
	private $smtp_username;
	private $smtp_password;
	private $smtp_port;
	private $smtp_timeout;

	public function __construct(string $protocol, string $host, string $username, string $password, int $port, int $timeout=30) {        
		$this->protocol = $protocol;
		$this->smtp_hostname = $host;
		$this->smtp_username = $username;
		$this->smtp_password = $password;
		$this->smtp_port = $port;
		$this->smtp_timeout = $timeout;
	}

	// Admin forgotten password
	public function send($message, $to, $subject) {
		$mail = new Mail();
		$mail->protocol = $this->protocol;
		$mail->smtp_hostname = $this->smtp_hostname;
		$mail->smtp_username = $this->smtp_username;
		$mail->smtp_password = $this->smtp_password;
		$mail->smtp_port = $this->smtp_port;
		$mail->smtp_timeout = $this->smtp_timeout;
		$mail->setFrom($this->smtp_username);
		$mail->setTo($to);
		$mail->setSender(html_entity_decode('Güngören Belediyesi', ENT_QUOTES, 'UTF-8'));
		$mail->setSubject($subject);
		$mail->setText($message);
		$mail->send();
	}
}

?>