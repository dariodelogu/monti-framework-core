<?php
	namespace App\System;

	use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
	use Laminas\Cache\Storage\Adapter\Filesystem;
	use Laminas\Cache\Storage\ClearByPrefixInterface;
	use Laminas\Cache\Storage\ClearExpiredInterface;
	use Laminas\Cache\Storage\StorageInterface;
	use Psr\SimpleCache\CacheInterface;

	class Cache {

		private static ?Cache $instance = null;

		/**
		 * @var StorageInterface  The storage adapter of the project
		 */
		private StorageInterface $adapter;

		/**
		 * @var CacheInterface  PSR-16 cache on the adapter
		 */
		private CacheInterface $psr_cache;

		public function __construct() {
			$this->adapter = $this->createAdapter();
			$this->psr_cache = new SimpleCacheDecorator($this->adapter);
		}

		/**
		 * Gets the cache of the project.
		 *
		 * @return     Cache
		 */
		public static function getInstance(): Cache {
			if(self::$instance === null) {
				self::$instance = new static();
			}
			return self::$instance;
		}

		/**
		 * Creates the storage adapter of the store chosen in config/cache.php.
		 *
		 * The store defines the adapter class in "adapter", all its other keys are the options passed to
		 * the adapter constructor, so any Laminas cache adapter can be used by changing the configuration
		 * (and installing its package).
		 *
		 * @return     StorageInterface
		 */
		private function createAdapter(): StorageInterface {
			$store_name = config("cache.default");
			$store_config = config("cache.stores." . $store_name);
			if($store_config === null) {
				throw new \InvalidArgumentException('Cache store "' . $store_name . '" is not defined in config/cache.php, run "php monti setup" to create the file');
			}
			$adapter_class = $store_config["adapter"] ?? null;
			if(!is_string($adapter_class) || !is_a($adapter_class, StorageInterface::class, true)) {
				throw new \InvalidArgumentException('Cache store "' . $store_name . '" needs a Laminas cache adapter class in "adapter", is its package installed?');
			}
			$options = $store_config;
			unset($options["adapter"]);
			if(is_a($adapter_class, Filesystem::class, true)) {
				$this->createFolder($options);
			}
			return new $adapter_class($options);
		}

		/**
		 * Creates the folder of the filesystem adapter, if the options define one and it is missing.
		 *
		 * @param      array  $options  The adapter options
		 *
		 * @return     void
		 */
		private function createFolder(array $options): void {
			$folder = $options["cache_dir"] ?? null;
			if($folder === null || is_dir($folder)) {
				return;
			}
			$permission = $options["dir_permission"] ?? 0775;
			mkdir($folder, $permission, true);
			//mkdir permissions are limited by the umask
			chmod($folder, $permission);
		}

		/**
		 * Gets the storage adapter of the project.
		 *
		 * @return     StorageInterface
		 */
		public static function getAdapter(): StorageInterface {
			return self::getInstance()->adapter;
		}

		/**
		 * Gets a value from the cache.
		 *
		 * @param      string  $key      The cache key (PSR-16 doesn't allow the characters {}()/\@:)
		 * @param      mixed   $default  Value returned if the key is missing or expired
		 *
		 * @return     mixed
		 */
		public static function get(string $key, mixed $default = null): mixed {
			return self::getInstance()->psr_cache->get($key, $default);
		}

		/**
		 * Sets a value in the cache.
		 *
		 * @param      string                    $key    The cache key (PSR-16 doesn't allow the characters {}()/\@:)
		 * @param      mixed                     $value  The value, it must be serializable
		 * @param      int|\DateInterval|null    $ttl    Lifetime in seconds, null for the one of config/cache.php
		 *
		 * @return     bool
		 */
		public static function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool {
			return self::getInstance()->psr_cache->set($key, $value, $ttl);
		}

		/**
		 * Empties the whole cache.
		 *
		 * @return     bool
		 */
		public static function flush(): bool {
			return self::getInstance()->adapter->flush();
		}

		/**
		 * Removes the keys that start with a prefix.
		 * If the adapter can't do it, the whole cache is emptied: the result is always correct, but less selective.
		 *
		 * @param      string  $prefix  The keys prefix
		 *
		 * @return     bool
		 */
		public static function deleteByPrefix(string $prefix): bool {
			$adapter = self::getInstance()->adapter;
			if($adapter instanceof ClearByPrefixInterface) {
				return $adapter->clearByPrefix($prefix);
			}
			return $adapter->flush();
		}

		/**
		 * Removes the expired entries. Adapters that expire the entries by themselves (e.g. Redis) have nothing to do.
		 *
		 * @return     void
		 */
		public static function prune(): void {
			$adapter = self::getInstance()->adapter;
			if($adapter instanceof ClearExpiredInterface) {
				$adapter->clearExpired();
			}
		}
	}
