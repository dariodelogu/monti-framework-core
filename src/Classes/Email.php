<?php

	/*
		***************************
		Visit https://github.com/PHPMailer/PHPMailer for more informations.
		***************************

		Constructor configuration example. You need to add these settings to /config/email.php, modifying the values ​​as needed.

		return [
			"PHPMailer" => [
				"host" => "smtp.somehost.com",
				"username" => "user",
				"password" => "****",
				"isSMTP" => true,
				"SMTPAuth" => true,
				"SMTPSecure" => \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
				"port" => 587,
				"SMTPOptions" => [
					"ssl" => [
						"verify_peer" => false,
						"verify_peer_name" => false,
						"allow_self_signed" => true,
					]
				],
			]
		];

		Then new \App\Classes\Email(config("email.PHPMailer"));
	*/

	namespace App\Classes;

	class Email extends \PHPMailer\PHPMailer\PHPMailer {
		
		/**
		 * Instantiate \App\Classes\Email object.
		 *
		 * @param      array  $options  Auth and connection options.
		 */
		public function __construct(array $options) {
			//Enable SMTP debugging
			// 0 = off (for production use)
			// 1 = client messages
			// 2 = client and server messages
			$this->SMTPDebug = 2;

			$this->Host = $options["host"];
			$this->SMTPAuth = $options["SMTPAuth"];
			$this->Username = $options["username"];
			$this->Password = $options["password"];
			$this->SMTPSecure = $options["SMTPSecure"];
			$this->Port = $options["port"];
			$this->IsHTML();
			if($options["isSMTP"]) {
				$this->IsSMTP();
			}
			try {
				$this->SMTPOptions = [
					"ssl" => $options["SMTPOptions"]["ssl"]
				];
			}
			catch(\Throwable $t) {}
		}
	}