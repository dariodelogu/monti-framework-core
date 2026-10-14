<?php
	namespace App\CLI;

	use App\System\CLI\Command;
	use App\System\CLI\Console;

	/**
	 * Removes the expired entries from the cache. It can be scheduled, e.g. with a cron job.
	 */
	class CachePrune extends Command {

		public function description(): string {
			return "Removes the expired cache entries";
		}

		public function handle(Console $console, array $arguments): int {
			\App\System\Cache::prune();
			$console->success("Expired entries removed");
			return 0;
		}
	}
