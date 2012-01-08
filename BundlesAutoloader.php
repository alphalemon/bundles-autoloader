/*
 * This file is part of the AlphaLemonThemeEngineBundle and it is distributed
 * under the MIT License. To use this bundle you must leave
 * intact this copyright notice.
 *
 * (c) Since 2011 AlphaLemon
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://alphalemon.com
 * 
 * @license    MIT License
 */

<?php
require_once __DIR__ . "/../symfony/src/Symfony/Component/Yaml/Yaml.php";
require_once __DIR__ . "/../symfony/src/Symfony/Component/Yaml/Escaper.php";
require_once __DIR__ . "/../symfony/src/Symfony/Component/Yaml/Inline.php";
require_once __DIR__ . "/../symfony/src/Symfony/Component/Yaml/Parser.php";
require_once __DIR__ . "/../symfony/src/Symfony/Component/Yaml/Unescaper.php";
require_once __DIR__ . "/../symfony/src/Symfony/Component/Yaml/Exception/ExceptionInterface.php";
require_once __DIR__ . "/../symfony/src/Symfony/Component/Yaml/Exception/ParseException.php";

use Symfony\Component\Yaml\Yaml;

abstract class BundlesAutoloader
{
    private $autoloaders;
    private $autoloadersPaths;
    
    abstract public function configure();
    
    public function __construct() 
    {
        $this->autoloadersPaths = $this->configure();
        $this->setupAutoloaders();
    }
    
    public function getNamespaces($dir)
    {
        $res = array();
        
        $namespaces = $this->processAutoloaders('namespace');
        foreach($namespaces as $bundleNamespaces)
        {
            foreach($bundleNamespaces as $bundleNamespace)
            {
                $res[$bundleNamespace['alias']] = $dir . $bundleNamespace['path'];
            }
        }
        
        return $res;
    }
    
    public function getBundles($environment = null)
    {
        $res = array();
        $bundles = $this->processAutoloaders('bundle'); 
        foreach($bundles as $bundle)
        {
            if(empty($bundle['environment']) || (in_array($environment, $bundle['environment'])))
            {
                $class = $bundle['class'];
                $res[] = new $class();
            }
        }
        
        return $res;
    }
    
    protected function setupAutoloaders()
    {
        foreach($this->autoloadersPaths as $autoloaderPath)
        {
            preg_match('/[\/|\\\][\w]+Bundle$/', $autoloaderPath, $match); 
            if(empty($match))
            {
                if ($handle = opendir($autoloaderPath))
                {
                    while (false !== ($file = readdir($handle)))
                    {
                        $fileName = $autoloaderPath . '/' . $file;
                        if ($file != '.' && $file != '..' && is_dir($fileName))
                        {
                            preg_match('/[\w+]Bundle$/', $file, $match);
                            if(!empty ($match)) $this->parseAutoloader($fileName);
                        }
                    }
                }
                closedir($handle);
            }
            else
            {
                $this->parseAutoloader($autoloaderPath);
            }   
        }
    }
    
    private function parseAutoloader($autoloaderPath)
    {
        $file = $autoloaderPath . '/Resources/config/autoloader.yml';
        if(is_file($file))
        {
            $this->autoloaders[] = Yaml::parse($file);
        }
        else 
        {
            $bundleName = basename($autoloaderPath);
            $file = $autoloaderPath . "/" . $bundleName . '.php';
            if(is_file($file))
            {
                $class = file_get_contents($file);
                preg_match('/namespace\s(.*?);/', $class, $match);
                $this->autoloaders[] = array($bundleName  => array('bundle' => array('class' => $match[1] . '\\' . $bundleName)));
            }
        }
    }
    
    protected function processAutoloaders($type)
    {
        $res = array();
        foreach($this->autoloaders as $autoloader)
        {
            foreach($autoloader as $bundleName => $value)
            {
                if(array_key_exists($type, $value)) $res[$bundleName] = $value[$type];
            }
        }
        
        return $res;
    }
}
