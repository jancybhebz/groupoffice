<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit20f8fba2b9b343af4207d86b14fb9a55
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInit20f8fba2b9b343af4207d86b14fb9a55', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit20f8fba2b9b343af4207d86b14fb9a55', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit20f8fba2b9b343af4207d86b14fb9a55::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
