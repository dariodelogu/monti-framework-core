<?php
	namespace App\System;

	class Project {

		public static $instance = null;
		public static array $modules = [];

		private static ?string $rootPath = null;

		public readonly string $name;
		public readonly string $description;

		public function __construct() {
			$app_conf = \App\System\Config::get(root_path("config/app.php"), "", []);
			$this->name = $app_conf["name"] ?? "";
			$this->description = $app_conf["description"] ?? "";
		}

		/**
		 * Get the current project.
		 *
		 * @return     null|\App\System\Project
		 */
		public static function get(): Project {
			if(self::$instance === null) {
				self::$instance = new static();
			}
			return self::$instance;
		}

		/**
		 * Set the project's root path.
		 *
		 * This package is installed as a Composer dependency, so it has no reliable way to find the consuming project's root on its own. The entry point (public/index.php) is expected to call this, once, before anything that resolves paths (root_path(), public_path(), config(), ...) is used.
		 *
		 * @param      string  $path  The project's root path.
		 *
		 * @return     void
		 */
		public static function setRootPath(string $path): void {
			self::$rootPath = get_absolute_path($path);
		}

		/**
		 * Get the project's root path.
		 *
		 * @throws     \Exception  If the root path hasn't been set yet via setRootPath()
		 *
		 * @return     string
		 */
		public static function getRootPath(): string {
			if(self::$rootPath === null) {
				throw new \Exception("Project root path has not been set. Call Project::setRootPath() before resolving any path.");
			}
			return self::$rootPath;
		}

		/**
		 * Bootstraps the whole application: environment, error handling, session,
		 * modules, the project's own boot hook, routing and dispatch.
		 *
		 * @return     void
		 */
		public static function init(): void {
			$env = parse_ini_file(root_path(".env"));
			if($env["TESTING"] == true) {
				ini_set('display_errors', 1);
				ini_set('display_startup_errors', 1);
				error_reporting(E_ALL);
			}

			//ensures that vardumper also works with ajax requests and adds a reference to the file that calls it
			\Symfony\Component\VarDumper\VarDumper::setHandler(function(...$vars) {
				static $initialized = false;

				if(!$initialized && PHP_SAPI !== 'cli') {
					header("Content-Type: text/html; charset=utf-8");
					$initialized = true;
				}

				$cloner = new \Symfony\Component\VarDumper\Cloner\VarCloner();
				$dumper = PHP_SAPI === 'cli'
					? new \Symfony\Component\VarDumper\Dumper\CliDumper()
					: new \Symfony\Component\VarDumper\Dumper\HtmlDumper();

				$trace = debug_backtrace();
				$trace = array_filter($trace, fn($item) => !strpos(($item["file"] ?? ""), "var-dumper"));
				$item = reset($trace) ?? [
					"file" => "",
					"line" => ""
				];
				$output = '';
				$first_line = true;

				$dumper->dump(
					$cloner->cloneVar($vars[0]),
					function(string $line, int $depth) use (&$output, $item, &$first_line): void {
						if($first_line) {
							$line .= " " . $item["file"] . ":" . $item["line"];
							$first_line = false;
						}
						if($depth > -1) {
							$output .= str_repeat('  ', $depth) . $line . "\r\n";
						}
						$first_line = $depth < 0;
					}
				);
				echo $output;
			});

			// create a log channel
			$log = new \Monolog\Logger('app');
			$log->pushHandler(new \Monolog\Handler\StreamHandler(root_path('logs/app.log'), \Monolog\Level::Warning));

			$whoops = new \Whoops\Run;
			$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
			$whoops->pushHandler(new \Whoops\Handler\CallbackHandler(function ($exception, $inspector, $run) use ($log) {
				$log->error($exception->getMessage(), [
					'file' => $exception->getFile(),
					'line' => $exception->getLine(),
					'trace' => $exception->getTraceAsString(),
				]);
			}));
			$whoops->register();

			$env = array_merge($_SERVER, $env);
			\App\System\Env::setFromArray($env);

			class_alias(\App\System\Session::class, "Session");
			class_alias(\App\System\Document\Document::class, "Document");
			class_alias(\App\System\Database::class, "DB");
			class_alias(\App\System\Translation\Language::class, "Language");
			class_alias(\App\System\Domain::class, "Domain");
			class_alias(\App\System\Project::class, "Project");

			\App\System\MVC\View\View::initSourcePaths();

			$conf = config("connections.default", null);
			if($conf !== null) {
				\App\System\Database::addConnection($conf, "default");
			}

			\App\System\Session::start();

			//init modules
			self::initPackages();
			\App\System\Translation\Language::init();

			//boot project
			self::boot();

			//shared framework routes, shipped with this package
			include(__DIR__ . "/../routes.php");

			//project-specific routes
			$project_routes = root_path("src/routes.php");
			if(file_exists($project_routes)) {
				include $project_routes;
			}

			\App\System\Events\Manager::getEvent("router.onRouteHandled")->addListener(function($route, $response) {
				//a redirect isn't a page the user actually saw
				if($response instanceof \App\System\Http\RedirectResponse) {
					return;
				}
				\App\System\Session::push_history($route);
			});

			\App\System\Events\Manager::getEvent("system.onBooted")->dispatch();

			abort_if(!\App\System\Router\CSRFVerifier::get()->validateRequest(\App\System\Http\ServerRequest::get()), 403);
			\App\System\Router\Router::start();

			\App\System\Session::delete("messages");
			\App\System\Session::delete("inputs");
		}

		public static function initPackages() {
			$cachePath = root_path('cache/providers.php');

			if(!is_file($cachePath)) {
				return false;
			}

			$classes = include $cachePath;

			foreach ($classes as $class) {
				$instance = new $class();
				$instance->init();
			}
		}

		/**
		 * Executes the project boot file if exists
		 */
		public static function boot() {
			$boot_file = root_path("src/boot.php");
			if(file_exists($boot_file)) {
				include $boot_file;
			}
		}

		/**
		 * Get the available languages for the project.
		 *
		 * @return array The list of available languages.
		 */
		public function getLanguages() {
			return config("app.languages.available", ["en"]);
		}

		/**
		 * Check if the project supports a specific language.
		 *
		 * @param string $lang The language code to check (e.g., "en", "it").
		 * @return bool True if the language is supported, false otherwise.
		 */
		public function hasLanguage(string $lang) {
			return in_array($lang, $this->getLanguages());
		}

		/**
		 * Get the default language for the project.
		 *
		 * @return string The default language.
		 */
		public function getDefaultLanguage() {
			return config("app.languages.default", "en");
		}

		/**
		 * Get the project active modules
		 *
		 * @return \Illuminate\Support\Collection Module fully qualified class names, keyed by module name.
		 */
		public function getModules() {
			return collect(self::$modules);
		}

		/**
		 * Get if a project has a module
		 *
		 * @param string $module       The module to check for
		 * @return boolean			   True if the project has the module, false otherwise.
		 */
		public function hasModule(string $module) {
			return isset(self::$modules[$module]);
		}

		public static function pushModule(string $name, string $fqcn) {
			self::$modules[$name] = $fqcn;
		}
	}