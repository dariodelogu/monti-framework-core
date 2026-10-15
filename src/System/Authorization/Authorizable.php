<?php
	namespace App\System\Authorization;

	/**
	 * Adds can() and canAny() to a model, the subject of the check is the model itself.
	 * The answer comes from the Authorizer, so the model doesn't know which module knows the permissions.
	 */
	trait Authorizable {

		/**
		 * Whether the model has the permission, or all the permissions if it is an array.
		 *
		 * @param      string|array  $permissions  The permission name, or all the names required
		 *
		 * @return     bool
		 */
		public function can(string|array $permissions): bool {
			return Authorizer::allows($permissions, $this);
		}

		/**
		 * Whether the model has at least one of the permissions.
		 *
		 * @param      array  $permissions  The permission names
		 *
		 * @return     bool
		 */
		public function canAny(array $permissions): bool {
			return Authorizer::allowsAny($permissions, $this);
		}
	}
