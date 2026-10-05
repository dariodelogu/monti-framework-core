<?php
	namespace App\System\Events;

	class Manager {
		/**
		 * @var array $events A static array to store all registered events.
		 */
		public static $events = [];

		/**
		 * Retrieves an existing event or creates a new one if it doesn't exist.
		 *
		 * @param string $event_name The name of the event to retrieve or create.
		 *                           The name will be processed by `\App\Classes\Str::remove_spaces()`.
		 * @return Event The event instance associated with the given name.
		 */
		 public static function getEvent(string $event_name) {
		    $event_name = \App\Classes\Str::remove_spaces($event_name);
		    if(isset(self::$events[$event_name])) {
		        return self::$events[$event_name];
		    }
		    $evt = new Event($event_name);
		    self::$events[$event_name] = $evt;
		    return self::$events[$event_name];
		}
	}