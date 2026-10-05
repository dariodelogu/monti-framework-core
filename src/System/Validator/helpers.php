<?php

	/**
	 * Shortcut for validator
	 *
	 * @param      ...$args  \App\System\Validator\Validator arguments
	 *
	 * @return     \App\System\Validator instance
	 */
	function validator(...$args) {
		return new \App\System\Validator\Validator(...$args);
	}