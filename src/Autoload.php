<?php

/**
 * Basic autoloader
 *
 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 */
class Autoload {

	/** @var string */
	private $path;

	/**
	 * Register autoloader to given path
	 *
	 * @param $path
	 */
	public function register($path = '') {
		$this->path = rtrim($path, DIRECTORY_SEPARATOR);

		spl_autoload_register(array($this, 'loadClass'));
	}

	/**
	 * @param $className
	 *
	 * @return bool
	 */
	function loadClass($className) {
		$className = ltrim($className, '\\');
		$fileName = $this->path.DIRECTORY_SEPARATOR;
		$lastNsPos = strripos($className, '\\');
		if ($lastNsPos !== false) {
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
		}
		$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';

		$filePath = stream_resolve_include_path($fileName);
		if ($filePath) {
			require $fileName;
		}
		return $fileName !== false;
	}

}
