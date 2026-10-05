<?php
	include __DIR__ . "/MVC/View/helpers.php";
	include __DIR__ . "/Router/helpers.php";
	include __DIR__ . "/Http/helpers.php";
	include __DIR__ . "/Validator/helpers.php";
	include __DIR__ . "/Translation/helpers.php";

	/**
	 * Get contents of a directory
	 *
	 * @param      string      $path
	 *
	 * @throws     \Exception  Thrown when $path is not a directory
	 *
	 * @return     array
	 */
	function content_of(string $path) : array {
		if(!is_dir($path)) {
			throw new \Exception("Given path is not a directory");
		}

		$d = opendir($path);
		$dir_content = [];
		while(($file = readdir($d)) !== false) {
			if($file != "." && $file != "..") {
				$dir_content[] = $file;
			}
		}
		return $dir_content;
	}

	/**
	 * Prints a detailed description of $var
	 *
	 * @param      mixed  $var    The variable
	 */
	function pretty(mixed $var) {
		dump(gettype($var) . ' ' . json_encode(
			$var,
			JSON_UNESCAPED_SLASHES | 
			JSON_UNESCAPED_UNICODE | 
			JSON_PRETTY_PRINT | 
			JSON_PARTIAL_OUTPUT_ON_ERROR | 
			JSON_INVALID_UTF8_SUBSTITUTE 
		)); 
	}

	/**
	 * file_get_contents with simulated user agent
	 *
	 * @param      string  $url    URL where to get contents
	 *
	 * @return     string
	 */
	function get_contents(string $url) {
		$agents = [
			"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.6 Safari/605.1.1",
			"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.3",
			"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.3",
			"Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.",
			"Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.",
			"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Edg/131.0.0.",
			"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 Herring/97.1.8280.8",
			"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.3",
			"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36 OPR/115.0.0.",
			"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36 AtContent/95.5.5462.5",
			"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36 Edge/18.1958",
			"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.3",
			"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36 OPR/114.0.0.",
			"Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.3",
		];
		$context = stream_context_create([
			"http" => [
				"user_agent" => $agents[array_rand($agents)]
			]
		]);
		return @file_get_contents($url, false, $context);
	}

	/**
	 * Makes a recursive copy of the contents of source path to destination path
	 *
	 * @param      string  $source  The source path
	 * @param      string  $dest    The destination path
	 */
	function recursive_copy(string $source, string $dest) {
		if(is_dir($source)) {
			@mkdir($dest, recursive: true);
			foreach(content_of($source) as $p) {
				recursive_copy($source . "/" . $p, $dest . "/" . $p);
			}
		}
		else {
			@copy($source, $dest);
		}
	}

	/**
	 * Converts an object to array
	 *
	 * @param      mixed        $item   The object to convert
	 *
	 * @return     array  The resulting array
	 */
	function object_to_array(mixed $item): array {
		if(is_object($item)) {
			return get_object_vars($item);
		}
		if(is_array($item)) {
			return array_map(__FUNCTION__, $item);
		}
		return $item;
	}

	/**
	 * Converts an array to object
	 *
	 * @param      mixed        $item   The array to convert
	 *
	 * @return     array  The resulting object
	 */
	function array_to_object(array $data) {
		return (object)array_map(__FUNCTION__, $data);
    }

    /**
     * Access items of an array using dot notation
     *
     * @param      string      $key      The multilevel key to read
     * @param      array       $array    The source array
     * @param      mixed|null  $default  Default value to return if missing key
     *
     * @return     mixed       Searched key value if found, $default otherwise
     */
    function dot_notation_array(string $key, array $array, mixed $default = null) {
		if(empty($key)) {
			return $array;
		}
		$path = explode(".", $key);
		if(!count($path)) {
			return $default;
		}
		if(!isset($array[$path[0]])) {
			return $default;
		}

		$level = $array[$path[0]];

		unset($path[0]);
		foreach($path as $k) {
			if(!isset($level[$k])) {
				return $default;
			}
			$level = $level[$k];
		}
		return $level;
	}

	/**
	 * Converts an array to attributes string
	 *
	 * @param      array   $attributes  Array of key => value attributes
	 *
	 * @return     string
	 */
	function generate_html_attributes(array $attributes) : string {
		$result = [];
		foreach($attributes as $name => $value) {
			if(is_string($name)) {
				$result[] = $name . '="' . htmlspecialchars($value ?? "") . '"';
			}
			if(is_int($name)) {
				$result[] = $value;
			}
		}
		return implode(" ", $result);
	}

	/**
	 * Minify the given CSS
	 *
	 * @param      string  $text
	 *
	 * @return     string  Minified CSS
	 */
	function minify_css(string $css) : string {
		$css = preg_replace('/\s+/', ' ', $css);
		$css = preg_replace('/\/\*.*?\*\//', '', $css);
		$css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
		$css = str_replace(';}', '}', $css);
		return $css;
	}

	/**
	 * Get a value from a confing in the "config" folder
	 *
	 * @param      string  $key      The config value key
	 * @param      mixed   $default  Default value to return if missing $key value
	 *
	 * @return     mixed
	 */
	function config(string $key = "", mixed $default = null) : mixed {
		$exp = explode(".", $key, 2);
		$path = root_path("config");
        return dot_notation_array($exp[1] ?? "", \App\System\Config::get("$path/$exp[0].php", "", []), $default);
	}

	/**
	 * This works like realpath but works even if the file doesn't exist
	 *
	 * @param      string  $path   The path
	 *
	 * @return     string  The absolute path
	 */
	function get_absolute_path(string $path) : string {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		$first_char = substr($path, 0, 1);
		$starts_with_dir_separator = $first_char === "/" || $first_char === DIRECTORY_SEPARATOR;
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            }
            else {
                $absolutes[] = $part;
            }
        }
        return ($starts_with_dir_separator ? DIRECTORY_SEPARATOR : "") . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

	/**
	 * Get an absolute path from root folder
	 *
	 * @param      string  $path   The path
	 *
	 * @return     string
	 */
	function root_path(string $path = "") : string {
		return get_absolute_path(\App\System\Project::getRootPath() . "/" . $path);
	}

	/**
	 * Get an absolute path from public folder
	 *
	 * @param      string  $path   The path
	 *
	 * @return     string
	 */
	function public_path(string $path = "") : string {
		return get_absolute_path(\App\System\Project::getRootPath() . "/public/" . $path);
	}

	/**
	 * Stops the code execution and sends an HTTP response code
	 *
	 * @param      int   $code   The HTTP code
	 */
	function abort(int $code, ?string $message = null) {
		http_response_code($code);
		$default_messages = [
			343 => __("You are developer! But this framework is mine!"),
			400 => __("Bad request"),
			403 => __("Access denied"),
			404 => __("Not found"),
			405 => __("Method not allowed"),
			418 => __("I'm a teapot"),
			500 => __("Server error"),
		];
		$data = [
			"code" => $code,
			"message" => $message ?? $default_messages[$code] ?? ""
		];
		if(\App\System\Http\ServerRequest::get()->isAjax()) {
			header("Content-type: application/json;");
			echo json_encode($data);
		}
		else {
			view("http_error", $data)->render();
		}
		exit();
	}

	/**
	 * Stops the code execution and sends an HTTP response code if $condition
	 *
	 * @param      bool  $condition
	 * @param      int   $code       The HTTP code
	 */
	function abort_if(bool $condition, int $code, ?string $message = null) {
		if($condition) {
			abort($code, $message);
		}
	}

	/**
	 * Sorts array by key value
	 * kv = key value
	 *
	 * @param      array   $array  The array to sort
	 * @param      $key    The key
	 * @param      $order  The ordering type
	 *
	 * @return     array
	 */
	function kvsort(array $array, $key, $order = SORT_ASC) {
		$arr = array_column($array, $key);
		array_multisort($arr, $order, $array);
		return $array;
	}

	/**
	 * Gets the last json error as readable string
	 *
	 * @param      array  $array  The array that failed the encoding
	 *
	 * @return     string  The json error
	 */
	function get_json_error(array $array) {
		if(!json_encode($array)) {
			$errors = [
				JSON_ERROR_NONE => 'No errors',
				JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
				JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
				JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
				JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
				JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
			];
			return $errors[json_last_error()] ?? 'Unknown error';
		}
	}

	/**
	 * Shortcut for \App\System\Env::get() method
	 *
	 * @param      string  $key          The string to translate
     * @param      mixed   $default      Default value to return if missing key
	 *
	 * @return     string  The env value
	 */
	function env_var(string $key = "", $default = null) {
		return \App\System\Env::get($key, $default);
	}