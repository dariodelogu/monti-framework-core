<?php
namespace App\System\MVC;

class Controller {

    /**
     * Loads the module routes if the routes.php file exists in the module directory.
     *
     * @return void
     */
    public function load_routes() {
        $routes_path = $this->getDir() . "/routes.php";
        if(file_exists($routes_path)) {
            include $routes_path;
        }
    }

    /**
     * Gets the directory of the current class file.
     *
     * @return string The directory path.
     */
    public function getDir() {
        $reflection = new \ReflectionClass(get_class($this));
        return dirname($reflection->getFileName());
    }

    /**
     * Gets the short class name of the current class.
     *
     * @return string The class name.
     */
    public function getClassName() {
        $reflection = new \ReflectionClass(get_class($this));
        return $reflection->getShortName();
    }

    /**
     * Copies resources from the module to the destination.
     *
     * @param string $namespace The namespace identifying the destination sub-paths.
     * @param array  $options {
     *     @type string $source      The source directory. Default is the module directory.
     *     @type array  $only        List of resource types to copy (e.g., ['views', 'locales', 'src', 'config.php', 'additional']).
     *     @type array  $additional  Additional files or directories to copy, as ['source_path' => 'relative_dest_path'].
     * }
     * @return void
     *
     * @throws \InvalidArgumentException If $namespace is empty.
     */
    public function copyResources(string $namespace, array $options): void
    {
        $namespace = trim($namespace);

        // Validates and normalizes the namespace string.
        if(empty($namespace)) {
            throw new \InvalidArgumentException(
                "Invalid resources namespace for controller {$this->getClassName()}"
            );
        }

        // Merges the provided options with the defaults.
        $options = array_merge([
            'source' => $this->getDir(),
            'only' => [],
            'additional' => [],
        ], $options);

        $this->copyDirs($namespace, $options);
        $this->copyFiles($namespace, $options);
        $this->copyAdditional($namespace, $options);
    }

    /**
     * Returns true if $key should be processed, based on the $only filter.
     *
     * @param  string   $key
     * @param  string[] $only
     * @return bool
     */
    private function isIncluded(string $key, array $only): bool
    {
        return empty($only) || in_array($key, $only, strict: true);
    }

    /**
     * Copies mapped directories (views, locales, src) to their destinations.
     *
     * @param  string $namespace
     * @param  array  $options
     * @return void
     */
    private function copyDirs(string $namespace, array $options): void
    {
        $map = [
            'views' => root_path("src/views/$namespace"),
            'locales' => root_path("locales/$namespace"),
            'src' => public_path('src'),
        ];

        foreach($map as $key => $dest) {
            if(!$this->isIncluded($key, $options['only'])) {
                continue;
            }

            $from = $options['source'] . DIRECTORY_SEPARATOR . $key;

            if(!is_dir($from)) {
                continue;
            }

            recursive_copy($from, $dest);
        }
    }

    /**
     * Copies mapped files (e.g. config.php) to their destinations.
     *
     * @param  string $namespace
     * @param  array  $options
     * @return void
     */
    private function copyFiles(string $namespace, array $options): void
    {
        $map = [
            'config.php' => root_path('config/' . strtolower($namespace) . '.php'),
        ];

        foreach($map as $key => $dest) {
            if(!$this->isIncluded($key, $options['only'])) {
                continue;
            }

            $from = $options['source'] . DIRECTORY_SEPARATOR . $key;

            if(!is_file($from)) {
                continue;
            }

            copy($from, $dest);
        }
    }

    /**
     * Copies additional files or directories defined in $options['additional'].
     * Destinations are relative to the namespace views directory.
     *
     * @param  string $namespace
     * @param  array  $options
     * @return void
     */
    private function copyAdditional(string $namespace, array $options): void
    {
        if(!$this->isIncluded('additional', $options['only'])) {
            return;
        }

        $base = root_path("src/views/$namespace");

        foreach($options['additional'] as $from => $relDest) {
            if(!file_exists($from)) {
                continue;
            }

            $dest = $base . DIRECTORY_SEPARATOR . $relDest;

            is_dir($from) ? recursive_copy($from, $dest) : copy($from, $dest);
        }
    }
}