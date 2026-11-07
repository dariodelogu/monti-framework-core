<?php
	namespace App\System\Authorization;

	/**
	 * Adds can() and canAny() to a model, checked with the Authorizer.
	 */
	trait Authorizable {

		/**
		 * Whether the model has the permission, or all the permissions of a list.
		 *
		 * @param      string|array  $permissions
		 *
		 * @return     bool
		 */
		public function can(string|array $permissions): bool {
			return Authorizer::allows($permissions, $this);
		}

		/**
		 * Whether the model has at least one of the permissions.
		 *
		 * @param      array  $permissions
		 *
		 * @return     bool
		 */
		public function canAny(array $permissions): bool {
			return Authorizer::allowsAny($permissions, $this);
		}
	}
