<?php
    namespace App\System\Validator\Rules;
    
    use Somnambulist\Components\Validation\Rule;

    class After extends Rule {
        protected string $message = 'rule.after';
        protected array $fillableParams = ['time'];

        public function check($value): bool
        {
            $time = $this->parameter('time');
            $target_date = $time;
            if($this->validation->input()->has($time)) {
                $target_date = $this->validation->input()->get($time);
            }
            try {
                $date_1 = new \DateTime($value);
                $date_2 = new \DateTime($target_date);
                return $date_1->getTimestamp() > $date_2->getTimestamp();
            }
            catch(\Throwable $t) {
                throw new \Exception($this->message);
            }
            return false;
        }
    }