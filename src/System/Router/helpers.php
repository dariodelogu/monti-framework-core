<?php

    /**
     * Generate a URL for a named route.
     *
     * @param string $name The name of the route.
     * @param array $parameters Optional parameters for the route.
     * @return string The generated URL.
     */
    function url(string $name, array $parameters = []): string {
        return \App\System\Router\Router::instance()->urlFor($name, $parameters);
    }

    /**
     * Get the singleton instance of the Router.
     *
     * @return \App\System\Router\Router The singleton instance of the Router.
     */
    function router() {
        return \App\System\Router\Router::instance();
    }

    /**
     * Redirect to the given URL, optionally flashing data to the session.
     *
     * @param string $url The destination URL.
     * @param array|null $data {
     *     Flash data stored in the session for the next request. Not sent as HTTP headers.
     *
     *     @type bool  $with_inputs Whether to flash the current request's input back to the session.
     *     @type array $messages    Messages to flash to the session.
     * }
     * @param int|null $code The HTTP status code for the redirect (default: 302).
     * @param array|null $headers Additional headers to include in the response.
     */
    function redirect(string $url, ?array $data = [], ?int $code = 302, ?array $headers = []): \Laminas\Diactoros\Response\RedirectResponse
    {
        \Session::delete("messages");
        //\Session::delete("inputs");
        if(isset($data["with_inputs"]) && is_bool($data["with_inputs"]) && $data["with_inputs"]) {
            \Session::set("inputs", \App\System\Http\ServerRequest::get()->all());
        }
        if(isset($data["messages"]) && is_array($data["messages"])) {
            \Session::set("messages", $data["messages"]);
        }

        return \App\System\Http\Response::redirect($url, $headers, $code);
    }

    /**
     * Get current csrf token
     * @return string|null
     */
    function csrf_token(): ?string
    {
        return \App\System\Router\CSRFVerifier::get()->getToken();
    }

	/**
	 * Prints the csrf input
	 */
	function csrf_input() {
		echo \App\System\Router\CSRFVerifier::get()->getTokenInput();
	}

	/**
	 * Prints the method input
	 *
	 * @param      string  $method
	 */
	function method_input(string $method) {
		$methods = [
			"PUT",
			"PATCH",
			"DELETE"
		];
		if(in_array(strtoupper($method), $methods)) {
			echo '<input type="hidden" name="_method" value="' . $method . '"/>';
		}
	}