<?php
	namespace App\System;

	/**
	 * Registry of the commands runnable with "php monti <command>".
	 *
	 * Every module registers its own commands from its Bootstrap::init() with register().
	 * Only the class names are stored, a command class is loaded when the command is run or listed.
	 */
	class CLI {

		/**
		 * @var array  Command name => Command class name
		 */
		private static array $commands = [];

		/**
		 * Registers commands.
		 *
		 * @param      array  $commands  Command name => fully qualified class name, which must extend \App\System\CLI\Command
		 *
		 * @throws     \Exception  If a name is already used by another class
		 *
		 * @return     void
		 */
		public static function register(array $commands): void {
			foreach($commands as $name => $class) {
				$class = ltrim($class, "\\");
				if(isset(self::$commands[$name]) && self::$commands[$name] !== $class) {
					throw new \Exception('Command "' . $name . '" is defined by both ' . self::$commands[$name] . " and " . $class);
				}
				self::$commands[$name] = $class;
			}
		}

		/**
		 * Gets the registered commands.
		 *
		 * @return     array  Command name => Command class name, sorted by name
		 */
		public static function commands(): array {
			ksort(self::$commands);
			return self::$commands;
		}
	}
