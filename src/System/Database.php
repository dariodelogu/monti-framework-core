<?php
	namespace App\System;

	use Illuminate\Database\Capsule\Manager as Capsule;
	use Illuminate\Events\Dispatcher;
	use Illuminate\Container\Container;

	class Database {

		public static $orm = null;

		/**
		 * Get the ORM instance.
		 *
		 * @return     \Illuminate\Database\Capsule\Manager
		 */
		public static function orm() {
			if(is_null(self::$orm)) {
				$orm = new Capsule;
				// Set the event dispatcher used by Eloquent models... (optional)
				$orm->setEventDispatcher(new Dispatcher(new Container));
				// Make this Capsule instance available globally via static methods... (optional)
				$orm->setAsGlobal();
				// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
				$orm->bootEloquent();
				self::$orm = $orm;
			}
			return self::$orm;
		}
		
		/**
		 * Determines if a connection is defined in the ORM.
		 *
		 * @param      string  $name   The connection name
		 *
		 * @return     bool    True if connection is defined, False otherwise.
		 */
		public static function hasConnection(string $name) {
			try {
				self::orm()->getConnection($name);
			}
			catch(\Throwable $t) {
				return false;
			}
			return true;
		}

		/**
		 * Determines if table exists
		 *
		 * @param      string  $table       The table name
		 * @param      string  $connection  The database connection to use
		 *
		 * @return     bool    True if table exists, False otherwise.
		 */
		public static function tableExists($table, $connection = "default") : bool {
			$sql = self::orm()->connection($connection)->select("SHOW TABLES LIKE '" . $table . "'");
			return count($sql) > 0;
		}

		/**
		 * Starts and wraps the code inside a database transaction.
		 * NOTE: Requires a compatible storage engine.
		 * $closure must return true on success: any other return value, or a thrown
		 * exception, rolls back everything the closure did within the transaction.
		 * $closure will have the transaction stdClass as first argument
		 *
		 * @example
		 * \App\System\Database::tansaction(function($transaction) {\
		 *     ...\
		 * });
		 *
		 * @param      \closure  $closure     The closure containing the code to wrap inside transacion
		 * @param      string    $connection  The ORM database connection
		 *
		 * @return     mixed
		 */
		public static function transaction(\closure $closure, string $connection = "default") {
			$transaction = new \stdClass();
			$transaction->result = false;
			$transaction->error = null;

			self::orm()->connection($connection)->transaction(function($conn) use ($closure, $transaction) {
				try {
					$transaction->result = $closure($transaction) ?? false;
				}
				catch(\Throwable $t) {
					$transaction->error = $t;
				}
				if($transaction->result !== true) {
					//closure failed (returned something other than true) or threw: roll back what it already did.
					// Eloquent's transaction() only auto-rollbacks on an uncaught exception, so this has to be explicit.
					$conn->rollBack();
				}
			});
			return $transaction;
		}

		/**
		 * Allows you to call the ORM's static methods through the Database class
		 * 
		 * @example
		 * \App\System\Database::table(...)->...;\
		 * \App\System\Database::addConnection(array $config, string $connection_name);\
		 * \App\System\Database::connection(...);
		 * \App\System\Database::raw(...);
		 *
		 * @param      string  $name       The method name
		 * @param      array   $arguments  The method arguments
		 *
		 * @return     mixed
		 */
		public static function __callStatic(string $name, array $arguments) {
			if(self::hasConnection("default")) {
				self::orm()->getDatabaseManager()->setDefaultConnection("default");
			}
			if(method_exists(self::orm(), $name)) {
				return self::orm()->$name(...$arguments);
			}
			try {
				$objs = [
					self::orm(),
					self::orm()->getConnection(self::orm()->getDatabaseManager()->getDefaultConnection())
				];
				foreach($objs as $o) {
					try {
						return $o->$name(...$arguments);
					}
					catch(\Throwable $t) {}
				}
			}
			catch(\Throwable $t) {}
		}
	}