<?php
    namespace App\System\Router;

    class Route {
		/**
		 * @var array $methods The HTTP methods supported by the route.
		 */
		private array $methods;

		/**
		 * @var string $path The path of the route.
		 */
		private string $path;

		/**
		 * @var callable|string $handler The handler for the route.
		 */
		private $handler;

		/**
		 * @var string|null $name The name of the route.
		 */
		private ?string $name = null;

		/**
		 * @var array $original_middlewares The original middlewares applied to the route.
		 */
		private array $original_middlewares = [];

		/**
		 * @var array $middlewares The list of middlewares applied to the route.
		 */
		private array $middlewares = [];

		/**
		 * @var RouteGroup|null $group The route group the route belongs to.
		 */
		private ?RouteGroup $group = null;

		/**
		 * @var string|null $namespace The namespace of the route.
		 */
		private ?string $namespace = null;

		/**
		 * @var array $params The parameters for the route.
		 */
		private array $params = [];

		/**
		 * Constructor for the Route class.
		 *
		 * @param array $methods The HTTP methods supported by the route.
		 * @param string $path The path of the route.
		 * @param callable|string $handler The handler for the route.
		 */
		public function __construct(array $methods, string $path, $handler) {
			$this->methods = $methods;
			$this->path = $path;
			$this->handler = $handler;
		}

		/**
		 * Get the HTTP methods supported by the route.
		 *
		 * @return array The HTTP methods supported by the route.
		 */
		public function getMethods() {
			return $this->methods;
		}

		/**
		 * Get the handler for the route.
		 *
		 * @return callable|string The handler for the route.
		 */
		public function getHandler() {
			return $this->handler;
		}

		/**
		 * Set the name of the route.
		 *
		 * @param string $name The name of the route.
		 * @return self The current Route instance.
		 */
		public function name(string $name): self {
			$this->name = $name;
			return $this;
		}

		/**
		 * Add a middleware to the route.
		 *
		 * @param string $middleware The middleware to add.
		 * @return self The current Route instance.
		 */
		public function middleware(string $middleware) {
			$this->original_middlewares[] = $middleware;
			$this->middlewares[] = $middleware;
			return $this;
		}

		/**
		 * Merge the middlewares from a route group.
		 *
		 * @param RouteGroup $group The route group to merge middlewares from.
		 */
		public function mergeGroupMiddlewares(RouteGroup $group): void {
			$this->middlewares = array_merge($group->getMiddlewares(), $this->middlewares);
		}

		/**
		 * Get the list of middlewares applied to the route.
		 *
		 * @return array The list of middlewares.
		 */
		public function getMiddlewares() {
			return $this->middlewares;
		}

		/**
		 * Set the namespace for the route.
		 *
		 * @param string|null $namespace The namespace to set.
		 * @return self The current Route instance.
		 */
		public function namespace(?string $namespace) {
			$this->namespace = $namespace;
			return $this;
		}

		/**
		 * Get the namespace of the route.
		 *
		 * @return string|null The namespace of the route.
		 */
		public function getNamespace() {
			return $this->namespace;
		}

		/**
		 * Get the name of the route.
		 *
		 * @return string|null The name of the route.
		 */
		public function getName() {
			return $this->name;
		}

		/**
		 * Set the route group the route belongs to.
		 *
		 * @param RouteGroup $group The route group to set.
		 */
		public function setGroup(RouteGroup $group) {
			$this->group = $group;
		}

		/**
		 * Get the route group the route belongs to.
		 *
		 * @return RouteGroup|null The route group the route belongs to.
		 */
		public function getGroup() {
			return $this->group;
		}

		/**
		 * Set the path of the route.
		 *
		 * @param string $path The path to set.
		 */
		public function setPath(string $path) {
			$this->path = $path;
		}

		/**
		 * Get the path of the route.
		 *
		 * @return string The path of the route.
		 */
		public function getPath() {
			return $this->path;
		}

		/**
		 * Set the parameters for the route.
		 *
		 * @param array $params The parameters to set.
		 */
		public function setParams(array $params) {
			$this->params = $params;
		}

		/**
		 * Get the parameters for the route.
		 *
		 * @return array The parameters for the route.
		 */
		public function getParams() {
			return $this->params;
		}

		/**
		 * Generate the URL for the route.
		 *
		 * @return string The generated URL.
		 * @throws \RuntimeException If a required parameter is missing.
		 */
		public function toUrl() {
			$usedParams = [];
			$path = $this->getPath();
			$params = $this->getParams();
			//Handling optional segments [/{param:regex}]
			$path = preg_replace_callback('/\[\/\{(\w+)(?::[^}]+)?\}\]/', function($matches) use ($params, &$usedParams) {
				$key = $matches[1];
				if(array_key_exists($key, $params)) {
					$usedParams[] = $key;
					return '/' . urlencode($params[$key]);
				}
				return ''; //If not present, remove the optional segment
			}, $path);

			//Handling required parameters {param:regex}
			$path = preg_replace_callback('/\{(\w+)(?::[^}]+)?\}/', function($matches) use ($params, &$usedParams) {
				$key = $matches[1];
				if(!array_key_exists($key, $params)) {
					throw new \RuntimeException("Required parameter '" . $key . "' is missing for route " . $this->name . ".");
				}
				$usedParams[] = $key;
				return urlencode($params[$key]);
			}, $path);

			//Extra parameters to query string
			$extraParams = array_diff_key($params, array_flip($usedParams));
			if(!empty($extraParams)) {
				$path .= '?' . http_build_query($extraParams);
			}

			return $path;
		}
	}