<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitdaa52061dd3cda2c3f68c9177946a852
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'M' => 
        array (
            'Marvel\\UtsLab\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'Marvel\\UtsLab\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInitdaa52061dd3cda2c3f68c9177946a852::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitdaa52061dd3cda2c3f68c9177946a852::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitdaa52061dd3cda2c3f68c9177946a852::$classMap;

        }, null, ClassLoader::class);
    }
}