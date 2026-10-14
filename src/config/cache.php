<?php
	/**
	 * Cache configuration.
	 *
	 * This is the default file of MontiCore, the setup copies it to config/cache.php
	 * of the project (an existing file is never overwritten): edit that copy.
	 *
	 * Nothing is cached automatically: a module caches only what it asks for with Cache::set().
	 */
	return [
		/**
		 * The store used by the project, one of the keys of "stores".
		 * Only this one is used: change it to switch between the configurations.
		 */
		"default" => "file",

		/**
		 * Each store has the "adapter" key, the class of a Laminas cache adapter (install its package
		 * with composer). All the other keys are the options of that adapter, see its documentation.
		 *
		 * The code that uses the cache is the same whatever the adapter is.
		 */
		"stores" => [
			"file" => [
				"adapter" => \Laminas\Cache\Storage\Adapter\Filesystem::class,

				/**
				 * Folder that contains the cached files, it is created if missing
				 * and must be writable by the web server
				 */
				"cache_dir" => root_path("cache/data"),

				/**
				 * Default lifetime in seconds of an entry, 0 means it never expires
				 */
				"ttl" => 0,

				/**
				 * Permissions of the created folders and files. The web server and the CLI
				 * can run with different users: both must be able to read and write them,
				 * restrict them (e.g. 0700 and 0600) when only one user is involved.
				 */
				"dir_permission" => 0775,
				"file_permission" => 0664
			]
		]
	];
