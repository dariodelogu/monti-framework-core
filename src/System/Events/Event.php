<?php
	namespace App\System\Events;

	class Event {
		private array $listeners = [];

		/**
		 * Event constructor.
		 *
		 * @param string $name The name of the event.
		 */
		public function __construct(private string $name) {}

		/**
		 * Adds a listener to the event.
		 *
		 * @param string|callable $handler The handler for the event. 
		 *                                 If a string is provided, it will be processed by `\App\Classes\Str::remove_spaces()`.
		 */
		public function addListener(string|callable $handler) {
	    	if(is_string($handler)) {
	    		$handler = \App\Classes\Str::remove_spaces($handler);
	    	}
	        $this->listeners[] = $handler;
	    }

	    /**
		 * Sets a single listener for the event, replacing any existing listeners.
		 *
		 * @param string|callable $handler The handler for the event. 
		 *                                 If a string is provided, it will be processed by `\App\Classes\Str::remove_spaces()`.
		 */
		public function setListener(string|callable $handler) {
			if (is_string($handler)) {
				$handler = \App\Classes\Str::remove_spaces($handler);
			}
			$this->listeners = [$handler];
		}

	    /**
		 * Dispatches the event to all registered listeners.
		 *
		 * @param mixed ...$arguments The arguments to pass to the listeners.
		 */
		public function dispatch(...$arguments) {
	        foreach ($this->listeners ?? [] as $handler) {
				if(is_string($handler) && class_exists($handler)) {
					$class = new $handler();
					$class->dispatch(...$arguments);
				}
				else if(is_callable($handler)) {
					$handler(...$arguments);
				}
	        }
	    }
	}