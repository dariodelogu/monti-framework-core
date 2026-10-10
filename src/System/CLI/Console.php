<?php
	namespace App\System\CLI;

	/**
	 * Minimal terminal input/output helper shared by the CLI features.
	 *
	 * Colors are used only when the output is a terminal, NO_COLOR is not set
	 * (https://no-color.org) or FORCE_COLOR is set.
	 */
	class Console {

		private const RESET = "\033[0m";
		private const BOLD = "\033[1m";
		private const DIM = "\033[2m";
		private const RED = "\033[31m";
		private const GREEN = "\033[32m";
		private const YELLOW = "\033[33m";
		private const CYAN = "\033[36m";

		/**
		 * Whether a person can answer prompts (stdin is a terminal).
		 *
		 * @return     bool
		 */
		public function isInteractive(): bool {
			return defined("STDIN") && stream_isatty(STDIN);
		}

		/**
		 * Whether colors can be used on the given stream.
		 *
		 * @param      resource  $stream
		 *
		 * @return     bool
		 */
		private function supportsColor($stream): bool {
			if(getenv("FORCE_COLOR") !== false) {
				return true;
			}
			return getenv("NO_COLOR") === false && stream_isatty($stream);
		}

		/**
		 * Wraps a text with ANSI codes when the stream supports colors.
		 *
		 * @param      string    $text
		 * @param      string    $codes   One or more of the class constants
		 * @param      resource  $stream
		 *
		 * @return     string
		 */
		private function style(string $text, string $codes, $stream = STDOUT): string {
			if(!$this->supportsColor($stream)) {
				return $text;
			}
			return $codes . $text . self::RESET;
		}

		/**
		 * Reads "key=value" lines from stdin, for non-interactive runs.
		 * Empty lines and lines starting with # are ignored.
		 *
		 * @return     array  key => value
		 */
		public function readStdinPairs(): array {
			$pairs = [];
			foreach(preg_split('/\R/', (string)stream_get_contents(STDIN)) as $row) {
				$row = trim($row);
				if($row === "" || str_starts_with($row, "#") || !str_contains($row, "=")) {
					continue;
				}
				[$key, $value] = explode("=", $row, 2);
				$pairs[trim($key)] = $value;
			}
			return $pairs;
		}

		/**
		 * Writes a line to stdout.
		 *
		 * @param      string  $text
		 */
		public function line(string $text = ""): void {
			fwrite(STDOUT, $text . PHP_EOL);
		}

		/**
		 * Writes a line to stderr.
		 *
		 * @param      string  $text
		 */
		public function error(string $text = ""): void {
			fwrite(STDERR, $text . PHP_EOL);
		}

		/**
		 * Writes a bold heading with a dim subtitle.
		 *
		 * @param      string  $text
		 * @param      string  $subtitle
		 */
		public function title(string $text, string $subtitle = ""): void {
			$line = $this->style($text, self::BOLD . self::CYAN);
			if($subtitle !== "") {
				$line .= $this->style("  " . $subtitle, self::DIM);
			}
			$this->line($line);
		}

		/**
		 * Writes a successful step.
		 *
		 * @param      string  $text
		 * @param      string  $detail  Dim text shown after the main one
		 */
		public function success(string $text, string $detail = ""): void {
			$this->line($this->step("✔", self::GREEN, $text, $detail));
		}

		/**
		 * Writes a failed step to stderr.
		 *
		 * @param      string  $text
		 * @param      string  $detail  Dim text shown after the main one
		 */
		public function failure(string $text, string $detail = ""): void {
			$this->error($this->step("✖", self::RED, $text, $detail, STDERR));
		}

		/**
		 * Writes a warning.
		 *
		 * @param      string  $text
		 */
		public function warning(string $text): void {
			$this->line($this->step("!", self::YELLOW, $text));
		}

		/**
		 * Writes a dim text.
		 *
		 * @param      string  $text
		 */
		public function muted(string $text): void {
			$this->line($this->style($text, self::DIM));
		}

		/**
		 * Builds an indented line made of a colored symbol, a text and an optional dim detail.
		 */
		private function step(string $symbol, string $color, string $text, string $detail = "", $stream = STDOUT): string {
			$line = "  " . $this->style($symbol, $color, $stream) . " " . $text;
			if($detail !== "") {
				$line .= $this->style("  " . $detail, self::DIM, $stream);
			}
			return $line;
		}

		/**
		 * Asks a question and reads the answer.
		 *
		 * @param      string  $label
		 * @param      bool    $secret  Show an asterisk for every typed character
		 *
		 * @return     string
		 */
		public function ask(string $label, bool $secret = false): string {
			fwrite(STDOUT, "  " . $this->style("?", self::CYAN) . " " . $label . $this->style(": ", self::DIM));
			if($secret) {
				return $this->readMasked();
			}
			$answer = fgets(STDIN);
			return $answer === false ? "" : rtrim($answer, "\r\n");
		}

		/**
		 * Reads a line from the terminal showing an asterisk for every typed character.
		 * Supports backspace and Ctrl+C. Falls back to a plain read if the terminal can't be configured.
		 *
		 * @return     string
		 */
		private function readMasked(): string {
			$saved_mode = trim((string)shell_exec("stty -g 2>/dev/null"));
			if($saved_mode === "") {
				$answer = fgets(STDIN);
				return $answer === false ? "" : rtrim($answer, "\r\n");
			}
			shell_exec("stty -icanon -echo -isig");
			$answer = "";
			try {
				while(($byte = fread(STDIN, 1)) !== false && $byte !== "") {
					if($byte === "\n" || $byte === "\r") {
						break;
					}
					if($byte === "\003") {
						shell_exec("stty " . escapeshellarg($saved_mode));
						fwrite(STDOUT, PHP_EOL);
						exit(130);
					}
					if($byte === "\177" || $byte === "\010") {
						if($answer !== "") {
							$answer = preg_replace('/.$/su', "", $answer);
							fwrite(STDOUT, "\010 \010");
						}
						continue;
					}
					if($byte === "\033") {
						$this->skipEscapeSequence();
						continue;
					}
					$answer .= $byte;
					//a multibyte character counts once: its continuation bytes (10xxxxxx) print nothing
					if((ord($byte) & 0xC0) !== 0x80) {
						fwrite(STDOUT, "*");
					}
				}
			}
			finally {
				shell_exec("stty " . escapeshellarg($saved_mode));
			}
			fwrite(STDOUT, PHP_EOL);
			return $answer;
		}

		/**
		 * Skips the rest of an escape sequence (e.g. arrow keys: ESC [ A), whose first byte was already read.
		 * A lone ESC key press, with nothing following, is ignored.
		 */
		private function skipEscapeSequence(): void {
			if(!$this->hasPendingInput() || ($introducer = fread(STDIN, 1)) === false) {
				return;
			}
			if($introducer === "O") {
				fread(STDIN, 1);
				return;
			}
			if($introducer !== "[") {
				return;
			}
			//a CSI sequence ends with a byte between "@" and "~"
			while(($byte = fread(STDIN, 1)) !== false && $byte !== "" && (ord($byte) < 0x40 || ord($byte) > 0x7E)) {
			}
		}

		/**
		 * Whether a byte is waiting on stdin (waits up to 50ms).
		 *
		 * @return     bool
		 */
		private function hasPendingInput(): bool {
			$read = [STDIN];
			$write = null;
			$except = null;
			return stream_select($read, $write, $except, 0, 50000) > 0;
		}
	}
