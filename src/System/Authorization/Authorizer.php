<?php
	namespace App\System\Authorization;

	use Illuminate\Database\Eloquent\Model;

	/**
	 * Central point of the authorization: the code asks whether a model (usually a user) has a permission
	 * and the modules register who knows the answer.
	 *
	 * A resolver answers true (allowed), false (denied) or null (not my business):
	 * the resolvers of the subject class are run by priority (higher first, then by registration order)
	 * and the first answer that is true or false is the final one. If nobody answers, the permission is denied.
	 */
	class Authorizer {

		/**
		 * @var array  Registered resolvers in registration order,
		 *             each one is ["model_class" => string, "resolver" => callable, "priority" => int]
		 */
		private static array $resolvers = [];

		/**
		 * Registers a function that answers whether a model has a permission.
		 * It also applies to the subclasses (and implementations) of the class.
		 *
		 * @param      string    $model_class  The class of the models that the resolver knows
		 * @param      callable  $resolver     function(Model $subject, string $permission): ?bool
		 *                                     true allows, false denies, null leaves the decision to the others
		 * @param      int       $priority     Higher priorities are run first
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
		 * The resolvers that apply to a subject: the ones registered for its class, its parent classes or its interfaces.
		 * They are sorted by priority, the ones with the same priority keep the registration order (the sort is stable).
		 *
		 * @param      Model  $subject  The model
		 *
		 * @return     array  Functions
		 */
		private static function resolversFor(Model $subject): array {
			$applicable = array_filter(self::$resolvers, fn($entry) => $subject instanceof $entry["model_class"]);
			usort($applicable, fn($first, $second) => $second["priority"] <=> $first["priority"]);
			return array_column($applicable, "resolver");
		}

		/**
		 * Whether the model has a permission, according to the first resolver that answers.
		 *
		 * @param      string  $permission  The permission name
		 * @param      Model   $subject     The model
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
		 * Whether the model has the permission, or all the permissions if it is an array.
		 * A null subject (e.g. nobody logged in) never has any permission: it is never replaced
		 * by someone else (e.g. the logged user), otherwise a missing model would be checked as another one.
		 * An empty list is never allowed.
		 *
		 * @param      string|array  $permissions  The permission name, or all the names required
		 * @param      Model|null    $subject      The model to check
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
		 * Whether the model has at least one of the permissions. An empty list is never allowed.
		 *
		 * @param      array       $permissions  The permission names
		 * @param      Model|null  $subject      The model to check
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
