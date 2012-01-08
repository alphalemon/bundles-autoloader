## Note: This class is just a demo and proposes a work solution without input and output checks, neither tested and
commented.

#Symfony2 BundlesAutoloader class
Registering a bundle with Symfony2 framework consists in two steps:

1. Add the bundle's namespace to the autoloader to let the framework know where the bundle is located
2. Register the bundle in the AppKernel class

The bundle registration is always delegated to the developer who want to use the bundle, and in the most of 
cases it is quite simple to perform those steps, while in other circumstances it could be a nightmare. This
is the case when a complex application that require several external bundles to work.

This class tries to  provide a way to semplify the registration of bundles introducing a single entry point 
where the bundle must be declared and delegating the responsability to set up the bundle's startup class and 
namespace to the bundle's developer himself.

## The BundlesAutoloader class
This abstract class is deputated to load the bundles and must be extended and a confugure method must be 
implemented to use it and the derived class must be placed inside the app folder of your application. This is
our entry point for each bunde.

An implementation of this class could be the following

    // app/AppBundlesAutoloader.php
    require_once __DIR__ . '/../vendor/bundles-autoloader/BundlesAutoloader.php';

    class AppBundlesAutoloader extends BundlesAutoloader {
        public function configure()
        {
            return array(__DIR__ . '/../vendor/bundles/AlphaLemon/PageTreeBundle',
                         __DIR__ . '/../vendor/bundles/AlphaLemon/ThemeEngineBundle',
                         __DIR__ . '/../vendor/bundles/AlphaLemon/AlValumUploaderBundle',
                         __DIR__ . '/../vendor/bundles/Propel/PropelBundle',
                        );
        }
    }

As you can see, that method must return an array of paths, where each path is referred to the bundle's position
on the application.

The AppKernel and autoload files
Those two files must be changed as follow:

    // app/AppKernel.php

    require_once 'AppBundlesAutoloader.php';
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            ...
        );
        
        $autoloader = new AppBundlesAutoloader();   
        $bundles = array_merge($bundles, $autoloader->getBundles($this->getEnvironment()));
        
        return $bundles;
    }

The bundles declared inside the AppBundlesAutoloader are parsed, instantiated and returned as an array
which is merged to the bundles declared as usual. The same is made for the autoload but in this case the 
namespaces are returned.

    // app/autoload.php

    require_once 'AppBundlesAutoloader.php';

    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(array(
        'Symfony'          => array(__DIR__.'/../vendor/symfony/src', __DIR__.'/../vendor/bundles'),
        'Sensio'           => __DIR__.'/../vendor/bundles',
        'JMS'              => __DIR__.'/../vendor/bundles',
        'Doctrine\\Common' => __DIR__.'/../vendor/doctrine-common/lib',
        'Doctrine\\DBAL'   => __DIR__.'/../vendor/doctrine-dbal/lib',
        'Doctrine'         => __DIR__.'/../vendor/doctrine/lib',
        'Monolog'          => __DIR__.'/../vendor/monolog/src',
        'Assetic'          => __DIR__.'/../vendor/assetic/src',
        'Metadata'         => __DIR__.'/../vendor/metadata/src',
    ));

    $autoloader = new AppBundlesAutoloader();
    $loader->registerNamespaces($autoloader->getNamespaces(__DIR__));


## The autoloader.yml file
At last the bundle's developer comes into play. He must create a file called autoloader.yml where must live inside the
Bundle/Resources/config folder. An implementation of that file could the following:

    ThemeEngine:
      namespace:
        0:
          alias: ThemeEngineCore
          path: /../vendor/bundles/AlphaLemon/ThemeEngineBundle/src
        1:
          alias: Themes
          path: /../vendor/bundles/AlphaLemon/ThemeEngineBundle
      bundle: 
        class: AlphaLemon\ThemeEngineBundle\AlphaLemonThemeEngineBundle
        #environment: 
        #  0: dev
        #  1: test

The file is quite easy to understand: there are two namespaces declared, ThemeEngineCore and Themes and it is 
specified the path where the namespace must point. The bundle section simply exposes the bundle's class name.

In addiction the bundle section could have an environment sub-section where the developer could declare the 
environments where the boundle is available.

## Parsing an entire folder
The autoloader is able to parse an entire folder, the one which usually represents the company, where bundles are 
placed and load them once a time. The previous example could be changed as follows:

    // app/AppBundlesAutoloader.php
    require_once __DIR__ . '/../vendor/bundles-autoloader/BundlesAutoloader.php';
    
    class AppBundlesAutoloader extends BundlesAutoloader {
        public function configure()
        {
            return array(__DIR__ . '/../vendor/bundles/AlphaLemon',
                         __DIR__ . '/../vendor/bundles/Propel/PropelBundle',
                        );
        }
    }