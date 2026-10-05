<?php
	namespace App\System;

	class Config {

		/**
		 * Get a config value.
		 *
		 * @param      string  $base_path    Absolute path to config file
		 * @param      string  $key      The variable name
		 * @param      mixed   $default  Default value if $key not found
		 *
		 * @return     mixed   The variable value
		 */
		public static function get(string $base_path, string $key = "", $default = null) {
			if(!self::exists($base_path, $key)) {
				return $default;
			}
			return dot_notation_array($key, include($base_path), $default);
		}

		/**
		 * Check if the config and/or key you are looking for exists
		 * 
		 * If returns "CONFIG_CLASS_KEY_CHECK_CONST" then config does not exists
		 *
		 * @param      string  $base_path  The base path to config file
		 * @param      string  $key        The key of the searched value
		 *
		 * @return     mixed   Searched value or $default
		 */
		public static function exists(string $base_path, string $key = ""): mixed {
			if(!file_exists($base_path)) {
				return false;
			}
			return dot_notation_array($key, include($base_path), "CONFIG_CLASS_KEY_CHECK_CONST") !== "CONFIG_CLASS_KEY_CHECK_CONST";
		}
	}