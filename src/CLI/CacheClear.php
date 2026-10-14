<?php
	namespace App\CLI;

	use App\System\CLI\Command;
	use App\System\CLI\Console;

	/**
	 * Empties the whole cache.
	 */
	class CacheClear extends Command {

		public function description(): string {
			return "Empties the cache";
		}

		public function handle(Console $console, array $arguments): int {
			if(!\App\System\Cache::flush()) {
				$console->failure("The cache can't be emptied");
				return 1;
			}
			$console->success("Cache emptied");
			return 0;
		}
	}
