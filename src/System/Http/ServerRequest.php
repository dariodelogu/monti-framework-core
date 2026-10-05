<?php
    namespace App\System\Http;

    class ServerRequest extends \Laminas\Diactoros\ServerRequest {

        /**
         * @var ServerRequest|null Singleton instance of the ServerRequest
         */
        public static $instance = null;

        /**
         * Get the singleton instance of the ServerRequest.
         * 
         * @return ServerRequest
         */
        public static function get() {
            if(self::$instance === null || !(self::$instance instanceof \App\System\Http\ServerRequest)) {
                self::$instance = ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
            }
            return self::$instance;
        }

        /**
         * Retrieve all query and body parameters.
         * 
         * @return array Merged array of query and body parameters.
         */
        public function all() {
            $params = $this->getQueryParams() ?? [];
            $body = $this->getParsedBody();

            if(empty($body)) {
                $raw_body = (string)$this->getBody();
                if(!empty($raw_body)) {
                    $decoded = json_decode($raw_body, true);
                    if(is_array($decoded) && json_last_error() === JSON_ERROR_NONE) {
                        $body = $decoded;
                    }
                    else {
                        parse_str($raw_body, $body);
                    }
                }
            }

            if(!is_array($body)) {
                $body = [];
            }

            return array_merge($params, $body);
        }

        /**
         * Retrieve a specific input value.
         * 
         * @param string $key The key to retrieve.
         * @param mixed $default The default value if the key does not exist.
         * @return mixed The value of the key or the default value.
         */
        public function input(string $key, mixed $default = null) {
            return $this->all()[$key] ?? $default;
        }

        /**
         * Check if a specific key is filled.
         * 
         * @param string $key The key to check.
         * @return bool True if the key exists, false otherwise.
         */
        public function filled(string $key) {
            return isset($this->all()[$key]);
        }

        /**
         * Add a parameter to the query string.
         * 
         * @param string $name The name of the parameter.
         * @param mixed $value The value of the parameter.
         * @return ServerRequest The updated request instance.
         */
        public function addGet(string $name, $value) {
            $request = clone ServerRequest::get();
            $data = $request->getQueryParams() ?? [];
            $data[$name] = $value;
            $request = $request->withQueryParams($data);
            ServerRequest::$instance = $request;
            return $request;
        }

        /**
         * Add a parameter to the request body.
         * 
         * @param string $name The name of the parameter.
         * @param mixed $value The value of the parameter.
         * @return ServerRequest The updated request instance.
         */
        public function addToBody(string $name, $value) {
            $request = clone ServerRequest::get();
            $data = $request->getParsedBody() ?? [];
            $data[$name] = $value;
            $request = $request->withParsedBody($data);
            ServerRequest::$instance = $request;
            return $request;
        }

        /**
         * Add a parameter to the request, either to the query string or body based on the HTTP method.
         * 
         * @param string $name The name of the parameter.
         * @param mixed $value The value of the parameter.
         * @return ServerRequest The updated request instance.
         */
        public function add(string $name, $value) {
            if(strtoupper($this->getMethod()) === "GET") {
                return $this->addGet($name, $value);
            }
            return $this->addToBody($name, $value);
        }

        /**
         * Add a file to the uploaded files.
         * 
         * @param string $path The file path.
         * @param string $name The file name.
         * @param string $mime_type The MIME type of the file.
         * @return ServerRequest The updated request instance.
         */
        public function addFile(string $path, string $name, string $mime_type) {
            $request = clone ServerRequest::get();
            $uploadedFiles = $request->getUploadedFiles();
            $newFile = new \Laminas\Diactoros\UploadedFile(
                $path,
                filesize($path),
                UPLOAD_ERR_OK,
                $name,
                $mime_type
            );
            $uploadedFiles[$name] = $newFile;
            $request = $request->withUploadedFiles($uploadedFiles);
            ServerRequest::$instance = $request;
            return $request;
        }

        /**
         * Check if the current request is an AJAX request.
         *
         * @return bool True if the request is an AJAX request, false otherwise.
         */
        public function isAjax() {
            return (\App\System\Http\ServerRequest::get()->getHeader("X-Requested-With")[0] ?? "") === "XMLHttpRequest";
        }
    }