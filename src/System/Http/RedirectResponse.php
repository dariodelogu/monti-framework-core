<?php
	namespace App\System\Http;

	class RedirectResponse extends \Laminas\Diactoros\Response\RedirectResponse {

		/**
		 * Creates a new instance.
		 *
		 * @param string $uri     The URL to redirect to.
		 * @param array  $headers Additional headers to include in the response.
		 * @param int    $status  The HTTP status code for the redirect (default: 302).
		 */
		public function __construct(string $uri, array $headers = [], int $status = 302) {
			parent::__construct($uri, $status, $headers);
		}

		/**
		 * Sends the HTTP response to the client.
		 *
		 * Sets the HTTP status code and headers, then send all headers and terminates the script.
		 *
		 * @return void
		 */
		public function send() {
			http_response_code($this->getStatusCode());
			foreach($this->getHeaders() as $name => $values) {
				foreach($values as $value) {
					header(sprintf('%s: %s', $name, $value), false);
				}
			}
			exit();
		}
	}