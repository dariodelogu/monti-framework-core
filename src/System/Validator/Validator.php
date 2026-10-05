<?php
	namespace App\System\Validator;

	class Validator {

		private $validator = null
		;

		public function __construct(array $data, array $rules) {
			$factory = new \Somnambulist\Components\Validation\Factory();
			$factory->addRule('before', new \App\System\Validator\Rules\Before());
			$factory->addRule('after', new \App\System\Validator\Rules\After());
			$this->validator = $factory->make($data, $rules);
			$this->validator->validate();
		}

		/**
		 * Gets if validation succeded.
		 *
		 * @return     bool  True on success, False otherwise.
		 */
		public function success() {
			return !$this->fails();
		}

		/**
		 * Gets if validation fails.
		 *
		 * @return     bool  True on fail, False otherwise.
		 */
		public function fails() {
			return $this->validator->fails();
		}

		/**
		 * Gets validation errors.
		 *
		 * @return     array
		 */
		public function failed() {
			//return $this->validator->errors();
			return $this->validator->getInvalidData();
		}
	}