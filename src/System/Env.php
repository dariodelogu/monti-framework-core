<?php
	namespace App\System;

	class Env {
		public static $env_vars = [];

		/**
		 * Set an environment variable.
		 *
		 * @param      string  $key    The variable name
		 * @param      mixed   $value  The variable value
		 */
		public static function set(string $key, $value) {
			self::$env_vars[$key] = $value;
		}

		/**
		 * Get an environment variable.
		 *
		 * @param      string  $key      The variable name
		 * @param      mixed   $default  Default value if $key not found
		 *
		 * @return     mixed   The variable value
		 */
		public static function get(string $key = "", $default = null) {
			if(empty($key)) {
				return self::$env_vars;
			}
			return self::$env_vars[$key] ?? $default;
		}

		/**
		 * Set environment variables from array.
		 *
		 * @param      array   $env    An array containing the key - values pairs
		 */
		public static function setFromArray(array $env) {
			foreach($env as $key => $val) {
				self::set($key, $val);
			}
		}
	}