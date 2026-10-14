<?php
	namespace App;

	class MontiCoreController extends \App\System\MVC\Controller {

		public function setup(array $options = []) {
			$this->copyResources("MontiCore", [
				"only" => ["src", "config"]
			]);
		}
	}
