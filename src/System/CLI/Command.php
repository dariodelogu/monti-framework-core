<?php
	namespace App\System\CLI;

	/**
	 * Base class of the commands runnable with "php monti <command>".
	 *
	 * A module exposes its commands as classes extending this one, placed in the CLI folder
	 * of its source directory (e.g. src/CLI/Setup.php) and registered from its Bootstrap
	 * with \App\System\CLI::register(["name" => Setup::class]).
	 */
	abstract class Command {

		/**
		 * A one-line description shown in the commands list.
		 *
		 * @return     string
		 */
		abstract public function description(): string;

		/**
		 * Runs the command.
		 *
		 * @param      Console  $console
		 * @param      array    $arguments  Arguments after the command name
		 *
		 * @return     int      The exit code, 0 on success
		 */
		abstract public function handle(Console $console, array $arguments): int;
	}
