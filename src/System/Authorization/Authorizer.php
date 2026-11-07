<?php
	namespace App\System\Authorization;

	use Illuminate\Database\Eloquent\Model;

	/**
	 * Answers whether a model, usually a user, has a permission, asking the registered resolvers.
	 */
	class Authorizer {

		/**
		 * @var array  ["model_class" => string, "resolver" => callable, "priority" => int]
		 */
		private static array $resolvers = [];

		/**
		 * Registers a resolver for a model class and its subclasses.
		 * It returns true (allowed), false (denied) or null (no answer): the resolvers with a higher priority run first,
		 * the first true or false is the answer, if there is none the permission is denied.
		 *
		 * @param      string    $model_class
		 * @param      callable  $resolver     function(Model $subject, string $permission): ?bool
		 * @param      int       $priority
		 *
		 * @return     void
		 */
		public static function resolver(string $model_class, callable $resolver, int $priority = 0): void {
			self::$resolvers[] = [
				"model_class" => $model_class,
				"resolver" => $resolver,
				"priority" => $priority
			];
		}

		/**
		 * The resolvers that apply to the subject, by priority.
		 *
		 * @param      Model  $subject
		 *
		 * @return     array
		 */
		private static function resolversFor(Model $subject): array {
			$applicable = array_filter(self::$resolvers, fn($entry) => $subject instanceof $entry["model_class"]);
			usort($applicable, fn($first, $second) => $second["priority"] <=> $first["priority"]);
			return array_column($applicable, "resolver");
		}

		/**
		 * Whether the subject has the permission, according to its resolvers.
		 *
		 * @param      string  $permission
		 * @param      Model   $subject
		 *
		 * @return     bool
		 */
		private static function check(string $permission, Model $subject): bool {
			foreach(self::resolversFor($subject) as $resolver) {
				$answer = $resolver($subject, $permission);
				if(is_bool($answer)) {
					return $answer;
				}
			}
			return false;
		}

		/**
		 * Whether the subject has the permission, or all the permissions of a list.
		 * A null subject or an empty list is never allowed.
		 *
		 * @param      string|array  $permissions
		 * @param      Model|null    $subject
		 *
		 * @return     bool
		 */
		public static function allows(string|array $permissions, ?Model $subject): bool {
			$permissions = (array)$permissions;
			if($subject === null || empty($permissions)) {
				return false;
			}
			foreach($permissions as $permission) {
				if(!self::check($permission, $subject)) {
					return false;
				}
			}
			return true;
		}

		/**
		 * Whether the subject has at least one of the permissions.
		 * A null subject or an empty list is never allowed.
		 *
		 * @param      array       $permissions
		 * @param      Model|null  $subject
		 *
		 * @return     bool
		 */
		public static function allowsAny(array $permissions, ?Model $subject): bool {
			if($subject === null) {
				return false;
			}
			foreach($permissions as $permission) {
				if(self::check($permission, $subject)) {
					return true;
				}
			}
			return false;
		}
	}
