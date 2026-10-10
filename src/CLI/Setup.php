<?php
	namespace App\CLI;

	use App\System\CLI\Command;
	use App\System\CLI\Console;

	/**
	 * Runs the setup() method of every module registered in the project.
	 *
	 * A module can declare the values it needs with setupOptions(), which returns
	 * ["option_name" => ["label" => string, "secret" => bool]]. Each value is taken from
	 * the stdin (lines "Module.option_name=value", when there's no terminal) or, when missing,
	 * asked on the terminal.
	 */
	class Setup extends Command {

		private Console $console;

		/**
		 * @var array  Values already available, keyed "Module.option_name"
		 */
		private array $provided = [];

		public function description(): string {
			return "Runs the setup of every installed module";
		}

		public function handle(Console $console, array $arguments): int {
			$this->console = $console;
			$this->provided = $console->isInteractive() ? [] : $console->readStdinPairs();
			return $this->run();
		}

		/**
		 * Runs the setup of all the modules, stopping at the first failure.
		 *
		 * @return     int  0 on success, 1 on failure
		 */
		private function run(): int {
			$started_at = microtime(true);
			$completed = [];
			$this->console->line();
			$this->console->title("Monti", "module setup");
			$this->console->line();
			foreach(\App\System\Project::get()->getModules() as $name => $class) {
				$module = new $class();
				if(!method_exists($module, "setup")) {
					continue;
				}
				try {
					$module->setup($this->collectOptions($name, $module));
				}
				catch(\Throwable $e) {
					$this->reportFailure($name, $e, $completed);
					return 1;
				}
				$completed[] = $name;
				$this->console->success($name);
			}
			$this->console->line();
			$this->console->success(
				count($completed) . " module(s) set up",
				sprintf("%.1fs", microtime(true) - $started_at)
			);
			$this->console->line();
			return 0;
		}

		/**
		 * Gets the values of the options declared by a module.
		 *
		 * @param      string  $name    Module name
		 * @param      object  $module
		 *
		 * @throws     \Exception  If a value is missing and there's no terminal to ask it
		 *
		 * @return     array
		 */
		private function collectOptions(string $name, object $module): array {
			if(!method_exists($module, "setupOptions")) {
				return [];
			}
			$options = [];
			foreach($module->setupOptions() as $option => $definition) {
				$key = $name . "." . $option;
				if(isset($this->provided[$key])) {
					$options[$option] = $this->provided[$key];
					continue;
				}
				if(!$this->console->isInteractive()) {
					throw new \Exception('Missing option "' . $key . '" and no terminal available to ask it');
				}
				$options[$option] = $this->console->ask($definition["label"] ?? $option, $definition["secret"] ?? false);
			}
			return $options;
		}

		/**
		 * Prints how far the setup got and why it stopped.
		 *
		 * @param      string      $name       Module that failed
		 * @param      \Throwable  $e
		 * @param      array       $completed  Modules already done
		 */
		private function reportFailure(string $name, \Throwable $e, array $completed): void {
			$this->console->failure($name, $e->getMessage());
			$this->console->error();
			$this->console->failure(
				"Setup stopped",
				"completed before the failure: " . (empty($completed) ? "none" : implode(", ", $completed))
			);
			$this->console->error();
		}
	}
