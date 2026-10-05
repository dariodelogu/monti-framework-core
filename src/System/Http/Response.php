<?php
	namespace App\System\Http;

	class Response extends \Laminas\Diactoros\Response {

		/**
		 * Set the body content of the response.
		 *
		 * @param mixed $body The content to write to the response body.
		 */
		public function setBody(mixed $body) {
			$this->getBody()->write($body);
		}

		/**
		 * Create a redirect response.
		 *
		 * @param string $location The URL to redirect to.
		 * @param array $headers Additional headers to include in the response.
		 * @param int $code The HTTP status code for the redirect (default: 302).
		 * @return \Laminas\Diactoros\Response\RedirectResponse
		 */
		public static function redirect(string $location, array $headers = [], int $code = 302) {
			return new RedirectResponse($location, $headers, $code);
		}

		/**
		 * Parse a response object and return a formatted HTTP response.
		 *
		 * @param mixed $response The response to parse. Can be of various types:
		 *                        - \App\System\Http\RedirectResponse: Returns as-is.
		 *                        - \App\System\MVC\View\View: Builds the view content.
		 *                        - \App\System\MVC\Model: Converts the model to JSON.
		 *                        - \stdClass: Converts the object to an array.
		 *                        - bool: Converts to "1" or an empty string.
		 *                        - array or \Illuminate\Support\Collection: Encodes to JSON.
		 * @return \App\System\Http\Response
		 */
		public static function parse($response) {
			$body = "";
			$is_json = false;
			if($response instanceof \App\System\Http\RedirectResponse) {
				return $response;
			}
			if(is_string($response)) {
				$body = $response;
			}
			else if($response instanceof \App\System\MVC\View\View) {
				$body = $response->build();
			}
			else if($response instanceof \App\System\MVC\Model) {
				$is_json = true;
				$body = $response->toJson();
			}
			else if($response instanceof \stdClass) {
				$body = object_to_array($response);
			}
			else if(is_bool($response)) {
				$body = $response ? "1" : "";
			}
			// return array || stdClass to array || collection
			if(is_array($response) || is_array($body) || $response instanceof \Illuminate\Support\Collection) {
				$is_json = true;
				$body = json_encode($response);
			}
			$final_response = new \App\System\Http\Response();
			if($is_json) {
				$final_response = $final_response->withHeader('Content-type', 'application/json');
			}
			$final_response->setBody($body);
			return $final_response;
		}
	}