<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;

require_once __DIR__.'/vendor/symfony/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->register();

$loader->registerNamespaces(array(
	'Nel' => __DIR__.'/src',
	'Ryzom' => __DIR__.'/src',
	'Symfony' => __DIR__.'/vendor/symfony',
));


return $loader;

