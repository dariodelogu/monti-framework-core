<?php
	namespace App\System\Translation;

	class Language extends \App\System\MVC\Model {

		/**
		 * The currently active language.
		 * 
		 * @var string|null
		 */
		public static $active_language = null;

		/**
		 * Initializes the language setting the memorized language and start translator.
		 */
		public static function init() {
			self::set(self::get());
			Translator::init();
		}

		/**
		 * Get the active language.
		 * Determines the language from cookie, HTTP header, or default config.
		 * 
		 * @return string The active language code.
		 */
		public static function get() {
			if(empty(self::$active_language)) {
				$languages = config("app.languages.available", ["en"]);
				$default = config("app.languages.default", "en");
				if(!empty($_COOKIE["active_language"]) && in_array($_COOKIE["active_language"], $languages)) {
					self::$active_language = $_COOKIE["active_language"];
				}
				$http_language = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"] ?? $default, 0, 2);
				if(empty(self::$active_language) && in_array($http_language, $languages)) {
					self::$active_language = $http_language;
				}
				if(empty(self::$active_language)) {
					self::$active_language = $default;
				}
			}
			return self::$active_language;
		}

		/**
		 * Set the active language.
		 * Updates session, cookies, environment, and locale.
		 * 
		 * @param string $new            The new language code to set.
		 * @param bool   $skip_cookie    Whether to skip setting the cookie. Defaults to false. If used inside loops prevents buffer filling and causing the application to crash.
		 *
		 */
		public static function set(string $new, bool $skip_cookie = false) {
			$old = self::get();
			if($old == $new || !in_array($new, \Project::get()->getLanguages())) {
				// even on a no-op language change, translations must be loaded at least once:
				// callers (e.g. route registration) may run __() before Language::init() gets the chance to
				if(empty(Translator::$translations)) {
					Translator::init();
				}
				return null;
			}
			self::$active_language = $new;
			\App\System\Session::set('lang', $new);

			if(!$skip_cookie) {
				// Set a cookie for the active language with a 1-year expiration
				setcookie("active_language", $new, time() + (60 * 60 * 24 * 365), "/");
			}

			// Update environment and locale settings
			putenv("LC_ALL=" . $new);
			setlocale(LC_ALL, $new);

			// Reinitialize the translator with the new language
			Translator::init();
		}
	}