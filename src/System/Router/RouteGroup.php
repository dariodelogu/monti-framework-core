<?php
    namespace App\System\Router;

    class RouteGroup {
        /**
         * @var string $prefix The prefix for the route group.
         */
        private string $prefix;

        /**
         * @var array $routes The list of routes in the group.
         */
        private array $routes = [];

        /**
         * @var RouteGroup|null $parent The parent route group, if any.
         */
        private ?RouteGroup $parent;

        /**
         * @var array $middlewares The list of middlewares applied to the group.
         */
        private array $middlewares = [];

        /**
         * @var string|null $namespace The namespace for the route group.
         */
        private ?string $namespace = null;

        /**
         * Constructor for the RouteGroup class.
         *
         * @param string $prefix The prefix for the route group.
         * @param RouteGroup|null $parent The parent route group, if any.
         */
        public function __construct(string $prefix = '', ?RouteGroup $parent = null) {
            $this->prefix = $prefix;
            $this->parent = $parent;
        }

        /**
         * Get the parent route group.
         *
         * @return RouteGroup|null The parent route group, or null if none exists.
         */
        public function getParent() {
            return $this->parent;
        }

        /**
         * Get the full prefix for the route group, including parent prefixes.
         *
         * @return string The full prefix for the route group.
         */
        public function fullPrefix(): string {
            $prefixes = [];
            $g = $this;
            while($g) {
                if($g->getPrefix()) {
                    $prefixes[] = trim($g->getPrefix(), '/');
                }
                $g = $g->getParent();
            }
            return '/' . implode('/', array_reverse($prefixes));
        }

        /**
         * Get the prefix for the route group.
         *
         * @return string The prefix for the route group.
         */
        public function getPrefix() {
            return $this->prefix;
        }

        /**
         * Get the list of routes in the group.
         *
         * @return array The list of routes in the group.
         */
        public function getRoutes() {
            return $this->routes;
        }

        /**
         * Add a route to the group.
         *
         * @param string|array $methods HTTP methods (e.g., GET, POST).
         * @param string $path The route path.
         * @param callable|string $handler The route handler.
         * @return Route The created route instance.
         */
        public function addRoute(string|array $methods, string $path, $handler): Route {
            if(is_string($methods)) {
				$methods = [$methods];
			}
            $methods = array_map("strtoupper", $methods);
			$route = Router::instance()->add($methods, $path, $handler);
            $full_path = rtrim($this->fullPrefix() . '/' . trim($route->getPath(), '/'), '/');

            if($full_path === '') {
                $full_path = '/';
            }

            $route->setPath(str_replace("//", "/", $full_path));
            $route->setGroup($this);
			$this->routes[] = $route;
			return $route;
        }

		/**
		 * Add a GET route to the group.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function get(string $path, $handler): Route {
			return $this->addRoute("GET", $path, $handler);
		}

		/**
		 * Add a POST route to the group.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function post(string $path, $handler): Route {
			return $this->addRoute("POST", $path, $handler);
		}

		/**
		 * Add a PATCH route to the group.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function patch(string $path, $handler): Route {
			return $this->addRoute("PATCH", $path, $handler);
		}

		/**
		 * Add a PUT route to the group.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function put(string $path, $handler): Route {
			return $this->addRoute("PUT", $path, $handler);
		}

		/**
		 * Add a HEAD route to the group.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function head(string $path, $handler): Route {
			return $this->addRoute("HEAD", $path, $handler);
		}

		/**
		 * Add a DELETE route to the group.
		 *
		 * @param string $path The route path.
		 * @param callable|string $handler The route handler.
		 * @return Route The created route instance.
		 */
		public function delete(string $path, $handler): Route {
			return $this->addRoute("DELETE", $path, $handler);
		}

        /**
         * Add a GET route that returns a view.
         *
         * @param string $url The URL of the route.
         * @param string $name The name of the view.
         * @param array $arguments Optional arguments to pass to the view.
         * @return Route The created route instance.
         */
        public function view(string $url, string $name, array $arguments = []) {
            return $this->addRoute("GET", $url, function() use ($name, $arguments) {
				return view($name, $arguments);
			});
		}

		/**
		 * Add a middleware to the group.
		 *
		 * @param string $middleware The middleware to add.
		 * @return $this The current RouteGroup instance.
		 */
		public function middleware(string $middleware) {
			$this->middlewares[] = $middleware;
            return $this;
		}

		/**
		 * Get the list of middlewares applied to the group.
		 *
		 * @return array The list of middlewares.
		 */
		public function getMiddlewares() {
			return $this->middlewares;
		}

        /**
         * Parse the routes in the group, applying parent middlewares and namespaces.
         *
         * @param array|null $routes The routes to parse. Defaults to the group's routes.
         */
        public function parseRoutes(null|array $routes = null) {
            $routes = $routes ?? $this->routes;
            if($parent = $this->parent) {
                if($parent->namespace !== null) {
                    $this->namespace($parent->namespace);
                }
            }
            foreach($routes as $route) {
                $route->mergeGroupMiddlewares($this);
                if($this->namespace !== null) {
                    $route->namespace($this->namespace);
                }
            }

            if($parent) {
                $parent->parseRoutes($this->routes);
            }
        }

		/**
		 * Set the namespace for the group.
		 *
		 * @param string $namespace The namespace to set.
		 * @return $this The current RouteGroup instance.
		 */
		public function namespace(string $namespace) {
			$this->namespace = $namespace;
            return $this;
		}
    }