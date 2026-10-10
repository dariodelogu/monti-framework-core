<?php
	namespace App\System\CLI;

	/**
	 * Runs the command requested on the command line, among the ones registered in \App\System\CLI.
	 */
	class Kernel {

		private Console $console;

		public function __construct(Console $console) {
			$this->console = $console;
		}

		/**
		 * Runs the command given on the command line.
		 *
		 * @param      array  $argv  The script arguments, the first one is the script itself
		 *
		 * @return     int    The exit code
		 */
		public function run(array $argv): int {
			$name = $argv[1] ?? "";
			$commands = \App\System\CLI::commands();
			if($name === "" || $name === "help") {
				$this->showCommands($commands);
				return $name === "" ? 1 : 0;
			}
			if(!isset($commands[$name])) {
				$this->console->error();
				$this->console->failure('Unknown command "' . $name . '"');
				$this->showCommands($commands, true);
				return 1;
			}
			$command = $this->instantiate($commands[$name]);
			if($command === null) {
				$this->console->failure('Command "' . $name . '" is registered with an invalid class', $commands[$name]);
				return 1;
			}
			return $command->handle($this->console, array_slice($argv, 2));
		}

		/**
		 * Creates a command, checking that the class exists and extends Command.
		 *
		 * @param      string        $class
		 *
		 * @return     Command|null  Null if the class is not a valid command
		 */
		private function instantiate(string $class): ?Command {
			if(!class_exists($class) || !is_subclass_of($class, Command::class)) {
				return null;
			}
			return new $class();
		}

		/**
		 * Prints the commands list.
		 *
		 * @param      array  $commands   Command name => Command class name
		 * @param      bool   $to_stderr
		 */
		private function showCommands(array $commands, bool $to_stderr = false): void {
			$write = $to_stderr ? fn($text) => $this->console->error($text) : fn($text) => $this->console->line($text);
			$width = max(array_map("strlen", array_keys($commands)) ?: [0]);
			$write("");
			$write("Usage: php monti <command>");
			$write("");
			$write("Commands:");
			foreach($commands as $name => $class) {
				$command = $this->instantiate($class);
				$write("  " . str_pad($name, $width + 2) . ($command === null ? "(invalid class " . $class . ")" : $command->description()));
			}
			$write("");
		}
	}
