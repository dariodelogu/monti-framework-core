<?php
	namespace App\System\Router;

    class CSRFVerifier {
        /**
         * @var string $key The session key used to store the CSRF token.
         */
        private string $key = "CSRFToken";

        /**
         * @var array $exceptions The list of URI patterns that are exceptions to CSRF validation.
         */
        private array $exceptions = [];

        /**
         * @var CSRFVerifier|null $instance The singleton instance of the CSRFVerifier.
         */
        public static $instance = null;

        /**
         * Get the singleton instance of the CSRFVerifier.
         *
         * @return CSRFVerifier The singleton instance of the CSRFVerifier.
         */
        public static function get() {
            if(self::$instance === null || !(self::$instance instanceof \App\System\Router\CSRFVerifier)) {
                self::$instance = new static;
            }
            return self::$instance;
        }

        /**
         * Constructor for the CSRFVerifier class.
         * Starts the session and initializes the CSRF token if not already set.
         */
        public function __construct() {
            \Session::start();
            if(!\Session::contains($this->key)) {
                \Session::set($this->key, bin2hex(random_bytes(32)));
            }
        }

        /**
         * Get the CSRF token from the session.
         *
         * @return string The CSRF token.
         */
        public function getToken(): string {
            return \Session::get($this->key, null);
        }

        /**
         * Add a URI pattern to the list of CSRF exceptions.
         *
         * @param string $pattern The URI pattern to add as an exception.
         */
        public function addException(string $pattern): void {
            $this->exceptions[] = $pattern;
        }

        /**
         * Check if a given URI matches any of the CSRF exceptions.
         *
         * @param string $uri The URI to check.
         * @return bool True if the URI matches an exception, false otherwise.
         */
        public function isException(string $uri): bool {
            foreach($this->exceptions as $pattern) {
                $regex = '#^' . str_replace(['*', '/'], ['.*', '\/'], $pattern) . '$#';
                if(preg_match($regex, $uri)) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Validate the CSRF token in the incoming request.
         *
         * @param \Laminas\Diactoros\ServerRequest $request The incoming server request.
         * @return bool True if the request is valid, false otherwise.
         */
        public function validateRequest(\Laminas\Diactoros\ServerRequest $request): bool {
            $params = $request->getServerParams();
            $uri = $params['REQUEST_URI'];
            if($this->isException($uri)) {
                return true;
            }

            $method = $params['REQUEST_METHOD'];
            if(in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                $token = $_POST[$this->key] ?? $params['HTTP_X_CSRF_TOKEN'] ?? '';
                return hash_equals($this->getToken(), $token);
            }

            return true;
        }

        /**
         * Generate an HTML input element containing the CSRF token.
         *
         * @return string The HTML input element with the CSRF token.
         */
        public function getTokenInput(): string {
            return '<input type="hidden" name="' . $this->key . '" value="' . htmlspecialchars($this->getToken()) . '">';
        }
    }