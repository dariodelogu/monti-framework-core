<?php

	namespace App\System\Translation;

	class Translator {

		/**
		 * @var array $translations
		 * Stores all loaded translations.
		 */
		public static $translations = [];

		/**
		 * Initializes the translations by loading locale files from modules.
		 */
		public static function init() {
			self::$translations = [];
			self::find_translations(root_path("locales"));
			foreach(\App\System\Project::get()->getModules() as $module) {
				self::find_translations((new $module)->getDir() . "/locales");
			}
		}

		/**
		 * Recursively loads locale files from a given path.
		 *
		 * @param string $path The directory or file path to search for locale files.
		 */
		public static function find_translations(string $path) {
			if(is_dir($path)) {
				foreach(content_of($path) as $p) {
					self::find_translations($path . DIRECTORY_SEPARATOR . $p);
				}
			}
			else {
				$info = pathinfo($path);
				$extension = $info["extension"] ?? "";
				if(in_array($extension, ["php", "json"])) {
					$exp = explode(".", $info["basename"]);
					$lang = $exp[count($exp) - 2];
					if(mb_strtolower($lang) == mb_strtolower(\Language::get())) {
						if($extension == "php") {
							self::$translations = array_merge(self::$translations, include $path);
						}
						if($extension == "json") {
							self::$translations = array_merge(self::$translations, json_decode(file_get_contents($path), true));
						}
					}
				}
			}
		}

		/**
		 * Translates a string using loaded translations and replaces placeholders.
		 *
		 * @param string $string		The string to translate.
		 * @param array $placeholders	Key-value pairs for placeholder replacement.
		 * @return string				The translated string.
		 */
		public static function translate(string $string, array $placeholders = []) {
			$keys = array_map(fn($key) => ":" . $key, array_keys($placeholders));
			$translation = self::$translations[$string] ?? $string;
			return str_replace($keys, $placeholders, $translation);
		}
	}