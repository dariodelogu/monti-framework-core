<?php

	/**
	 * Shortcut for translator translate method
	 *
	 * @param      string  $string        The string to translate
	 * @param      array   $placeholders  The placeholders
	 * __("Hello :subject!", ["subject" => "World"]);
	 *
	 * @return     string  The translated string
	 */
	function __(string $string, array $placeholders = []) : string {
		return \App\System\Translation\Translator::translate($string, $placeholders);
	}