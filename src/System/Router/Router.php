<?php
	namespace App\System\Router;

	use FastRoute\RouteCollector;
	use FastRoute\Dispatcher;
	use function FastRoute\simpleDispatcher;

	use Laminas\Diactoros\Response;
	use Laminas\Diactoros\ServerRequestFactory;
	use Psr\Http\Message\ServerRequestInterface;
	use Psr\Http\Message\ResponseInterface;
	use Psr\Http\Server\RequestHandlerInterface;

	class Router implements RequestHandlerInterface {
		/**
		 * @var array $routes The list of registered routes.
		 */
		private array $routes = [];

		/**
		 * @var array $groups The list of route groups.
		 */
		private array $groups = [];

		/**
		 * @var Dispatcher $dispatcher The FastRoute dispatcher instance.
		 */
		private Dispatcher $dispatcher;

		/**
		 * @var Router|null $instance The singleton instance of the Router.
		 */
		public static $instance = null;

		/**
		 * Get or create the singleton instance of the Router.
		 *
		 * @return Router The singleton instance of the Router.
		 */
		public static function instance() {
			if (self::$instance === null || !(self::$instance instanceof \App\System\Router\Router)) {
				self::$instance = new \App\System\Router\Router();
			}
			return self::$instance;
		}

		/**
		 * Start the router, build the dispatcher, and handle the request.
		 */
		public static function start() {
			self::instance()->buildDispatcher();

			// Create PSR-7 request using Laminas Diactoros
			$response = self::instance()->handle(\App\System\Http\ServerRequest::get());

			// Emit the response
			http_response_code($response->getStatusCode());
			foreach ($response->getHeaders() as $name => $values) {
				foreach ($values as $value) {
					header("$name: $value", false);
				}
			}
			echo $response->getBody();
		}

		/**
		 * Add a GET route.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function get(string $path, $handler): Route {
			return $this->add("GET", $path, $handler);
		}

		/**
		 * Add a POST route.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function post(string $path, $handler): Route {
			return $this->add("POST", $path, $handler);
		}

		/**
		 * Add a PATCH route.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function patch(string $path, $handler): Route {
			return $this->add("PATCH", $path, $handler);
		}

		/**
		 * Add a PUT route.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function put(string $path, $handler): Route {
			return $this->add("PUT", $path, $handler);
		}

		/**
		 * Add a HEAD route.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function head(string $path, $handler): Route {
			return $this->add("HEAD", $path, $handler);
		}

		/**
		 * Add a DELETE route.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function delete(string $path, $handler): Route {
			return $this->add("DELETE", $path, $handler);
		}

		/**
		 * Shortcut for a GET route that returns a view.
		 *
		 * @param string $url The URL of the route.
		 * @param string $name The name of the view.
		 * @param array $arguments Optional arguments to pass to the view.
		 * @return \League\Route\Route The created route instance.
		 */
		public function view(string $url, string $name, array $arguments = []) {
			return $this->get($url, function() use ($name, $arguments) {
				return view($name, $arguments);
			});
		}

		/**
		 * Add a route to the router.
		 *
		 * @param string|array $methods HTTP methods (e.g., GET, POST).
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function add(string|array $methods, string $path, $handler): Route {
			if(is_string($methods)) {
				$methods = [$methods];
			}
			$methods = array_map("strtoupper", $methods);
			$route = new Route($methods, $path, $handler);
			$this->routes[] = $route;
			return $route;
		}

		/**
		 * Add a CSRF exception.
		 *
		 * @param string $exception The exception to add.
		 */
		public static function addCSRFException(string $exception) {
			CSRFVerifier::get()->addException($exception);
		}

		/**
		 * Get the current route.
		 *
		 * @return mixed The current route.
		 */
		public function currentRoute() {
			return \App\System\Env::get("current_route");
		}

		/**
		 * Create a route group with a common prefix.
		 *
		 * @param string $prefix The group prefix.
		 * @param callable $callback The callback to define group routes.
		 * @return RouteGroup The created route group instance.
		 */
		public function group(string $prefix, callable $callback): RouteGroup {
			$count = count($this->groups);
			$parent = $this->groups[$count - 1] ?? null;
			$group = new RouteGroup($prefix, $parent);
			$this->groups[] = $group;
			$callback($group, $this);

			// Fix route path building
			$search = array_search($group, $this->groups);
			if($search !== false) {
				unset($this->groups[$search]);
				$this->groups = array_values($this->groups);
			}
			return $group;
		}

		/**
		 * Build the route dispatcher.
		 */
		public function buildDispatcher(): void {
			$this->dispatcher = simpleDispatcher(function(RouteCollector $r) {
				foreach($this->routes as $route) {
					foreach($route->getMethods() as $m) {
						$r->addRoute($m, $route->getPath(), $route);
					}
				}
			});
		}

		/**
		 * Parse a callable for a route.
		 *
		 * @param Route $route The route instance.
		 * @param callable|string|array $callable The callable to parse.
		 * @param array $request_params The request parameters.
		 * @return array Parsed callable details.
		 */
		private function parseCallable($route, $callable, $request_params) {
			$is_class = false;
			$class = null;
			$method = null;
			if(is_callable($callable)) {
				$reflect = new \ReflectionFunction($callable);
			}
			else if(is_string($callable) && str_contains($callable, '@')) {
				[$class, $method] = explode('@', $callable);
				if($route->getNamespace() !== null) {
					$class = $route->getNamespace() . "\\" . $class;
				}
				$reflect = new \ReflectionMethod($class, $method);
				$is_class = true;
			}
			else if(is_array($callable)) {
				$class = $callable[0];
				$method = $callable[1];
				$reflect = new \ReflectionMethod($class, $method);
				$is_class = true;
			}
			else {
				abort(500);
			}
			$callable_params = $reflect->getParameters();
			//sets optional params to null
			foreach($callable_params as $index => $param) {
				if(empty($request_params[$index])) {
					$request_params[$index] = null;
				}
			}
			foreach($callable_params as $index => $param) {
				if($param->getType()) {
					if($param->getType()->getName() == \App\System\Http\ServerRequest::class) {
						array_splice($request_params, $index, 0, [\App\System\Http\ServerRequest::get()]);
						break;
					}
				}
			}
			return [
				"is_class" => $is_class,
				"class" => $class,
				"method" => $method,
				"params" => $request_params,
				"callable" => $callable
			];
		}

		public function matchRoute(string $method, string $path) {
			$info = $this->dispatcher->dispatch($method, $path);
			$result = [
				"status" => 200,
				"route" => $info[1] ?? null,
				"params" => $info[2] ?? []
			];
			if($info[0] == Dispatcher::NOT_FOUND) {
				$result["status"] = 404;
			}
			if($info[0] == Dispatcher::METHOD_NOT_ALLOWED) {
				$result["status"] = 405;
			}
			return $result;
		}

		/**
		 * Handle an incoming request.
		 *
		 * @param ServerRequestInterface $request The PSR-7 request.
		 * @return ResponseInterface The response generated by the route handler.
		 */
		public function handle(ServerRequestInterface $request): ResponseInterface {
			$method = $request->input("_method") ?? $request->getMethod();

			// Normalize the path by removing the trailing slash, except for the root path
			$path = $request->getUri()->getPath();
			$path = rtrim($path, '/');
			$path = empty($path) ? '/' : $path;

			$dispatcher_result = $this->matchRoute($method, $path);

			if($dispatcher_result["status"] === 404) {
				\App\System\Events\Manager::getEvent("router.onNotFound")->dispatch($method, $path);
				abort(404, "Route not found");
			}
			abort_if($dispatcher_result["status"] === 405, 405);

			$route = $dispatcher_result["route"];
			$route->setParams($dispatcher_result["params"]);
			if($route->getGroup()) {
				$route->getGroup()->parseRoutes();
			}

			\App\System\Env::set("current_route", $route);
			\App\System\Events\Manager::getEvent("router.onRouteMatch")->dispatch($route);

			$parsed_callable = $this->parseCallable($route, $route->getHandler(), $route->getParams());

			$handler = new class($parsed_callable) implements RequestHandlerInterface {
				private $class = null;
				private $method = null;
				private $params = [];
				private $callable = null;

				public function __construct(array $parsed) {
					$this->class = $parsed["class"];
					$this->method = $parsed["method"];
					$this->params = $parsed["params"];
					$this->callable = $parsed["callable"];
				}

				public function handle(ServerRequestInterface $request): ResponseInterface {
					if($this->class === null && $this->callable !== null) {
						$response = ($this->callable)(...array_values($this->params));
					}
					else if($this->class !== null) {
						$method = $this->method;
						$response = (new $this->class)->$method(...array_values($this->params));
					}
					else {
						throw new \Exception("Invalid route handler");
					}
					return \App\System\Http\Response::parse($response);
				}
			};

			$middlewares_dispatcher = new MiddlewaresDispatcher($route->getMiddlewares(), $handler);
			$response = $middlewares_dispatcher->handle(\App\System\Http\ServerRequest::get());

			\App\System\Events\Manager::getEvent("router.onRouteHandled")->dispatch($route, $response);

			if($response instanceof \App\System\Http\RedirectResponse) {
				$response->send();
			}
			return $response;
		}

		/**
		 * Generate a URL for a named route.
		 *
		 * @param string $name The name of the route.
		 * @param array $params Optional parameters for the route.
		 * @return string The generated URL.
		 * @throws \Exception If the route is not found.
		 */
		public function urlFor(string $name, array $params = []): string {
			$allRoutes = array_merge($this->routes, ...array_map(fn($g) => $g->getRoutes(), $this->groups));
			foreach($allRoutes as $route) {
				if($route->getName() === $name) {
					$r = clone $route;
					$r->setParams($params);
					return $r->toUrl();
				}
			}
			throw new \Exception("Route '$name' not found");
		}

		/**
		 * Generate the URL a route would have in another language.
		 *
		 * Routes are registered only for the language active at boot (route paths are built with translated slugs via __()), so the route table for any other language simply doesn't exist in the current request.
		 * To get it, route registration is replayed into a throwaway Router while that language is temporarily active, and the singleton is swapped back afterwards.
		 * Reverse routing (urlFor()) never touches the FastRoute dispatcher, so this never affects how the current request is (or was) dispatched.
		 *
		 * @param string $lang The target language.
		 * @param Route $route The route to translate (its name and bound params are reused as-is).
		 * @return string|null The generated URL, or null if the route doesn't exist in that language.
		 */
		public function urlForLanguage(string $lang, Route $route): ?string {
			if($lang === \Language::get()) {
				return $route->toUrl();
			}

			$original_router = self::$instance;
			$original_lang = \Language::get();

			self::$instance = new Router();
			\Language::set($lang, true);

			include(\App\System\Project::mainRoutesPath());
			$project_routes = \App\System\Project::projectRoutesPath();
			if(file_exists($project_routes)) {
				include $project_routes;
			}
			foreach(\App\System\Project::get()->getModules() as $fqcn) {
				(new $fqcn())->load_routes();
			}

			$url = null;
			try {
				$url = self::$instance->urlFor($route->getName(), $route->getParams());
			}
			catch(\Exception $e) {}

			self::$instance = $original_router;
			\Language::set($original_lang, true);

			return $url;
		}
	}