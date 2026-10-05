<?php
	namespace App\System\MVC;

	class Model extends \Illuminate\Database\Eloquent\Model {

		protected $casts = [
					"id" => "integer",
					"deleted_at" => "datetime",
					"deletion_user" => "integer"
				],
				$connection = "default"
		;
	
		protected static function boot() {
			parent::boot();
			static::observe(\App\System\Observers\ModelsObserver::class);
		}

		/**
		 * Illuminate\Database\Eloquent\SoftDeletes updates deleted_at only
		 * See main/System/Observers/ModelsObserver.php deleting()
		 */
		public function _delete(?string $date = null, string $connection = "default") {
			if(\DB::connection($connection)->getSchemaBuilder()->hasColumn($this->table, 'deleted_at')) {
				$this->deleted_at = $date ?? date("Y-m-d H:i:s");
			}
			if(\DB::connection($connection)->getSchemaBuilder()->hasColumn($this->table, 'deletion_user')) {
				$this->deletion_user = \Auth::id() ?? 0;
			}
			return $this->save();
		}

		/**
		 * Finds a model or response with 404
		 *
		 * @param      int|string  $id     The model id
		 *
		 * @return     An object that represents the model
		 */
		public static function findOr404(int|string $id) {
			$find = static::find($id);
			abort_if(!$find, 404);
			return $find;
		}

		/**
		 * General method that returns the model user
		 *
		 * @return     \App\Modules\Users\User
		 */
		public function user() {
			return $this->belongsTo(\App\Modules\Users\User::class, "user_id");
		}

		/**
		 * Generic method that returns the user who created the record
		 *
		 * @return     \App\Modules\Users\User
		 */
		public function creationUser() {
			return $this->belongsTo(\App\Modules\Users\User::class, "creation_user")->withTrashed();
		}
	}