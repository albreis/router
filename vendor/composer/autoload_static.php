<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2d517e8c984f80b618e3466c2e1cc12f
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'Albreis\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Albreis\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2d517e8c984f80b618e3466c2e1cc12f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2d517e8c984f80b618e3466c2e1cc12f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2d517e8c984f80b618e3466c2e1cc12f::$classMap;

        }, null, ClassLoader::class);
    }
}
