<?php
/**
 * (C) 2016 Weblocks project.
 * This software is released under the GPL, see LICENSE.
 * https://opensource.org/licenses/gpl-license.php
 */
declare(strict_types = 1);

use Phalcon\Mvc\ViewBaseInterface;

class Application extends Phalcon\Mvc\Application
{
    const APP_PATH = __DIR__;

    private function loaders()
    {
        $paths = [];
        $files = scandir(self::APP_PATH);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = self::APP_PATH . '/' . $file;

            if (is_dir($path)) {
                $paths[] = $path;
            }
        }
        $loader = new Phalcon\Loader();
        $loader->registerDirs($paths);
        $loader->register();
    }
    private function services()
    {
        $di = new Phalcon\Di\FactoryDefault();
        $di->setShared(
            'db',
            function() {
                $config_file = __DIR__ . '/../conf/database.ini';
                if (!file_exists($config_file)) {
                    return new Exception($config_file . ' not exists.');
                }
                $factory = new Phalcon\Config\ConfigFactory();
                $options = $factory->load($config_file);
                $adapter = new Phalcon\Db\Adapter\PdoFactory();
                return $adapter->load($options);
            }
        );
        $di->setShared(
            'session',
            function() {
                $session = new Phalcon\Session\Manager();
                $files = new Phalcon\Session\Adapter\Stream();
                $session
                    ->setAdapter($files)
                    ->start();
                return $session;
            }
        );
        $di->set(
            'trans',
            function($language) {
                return new Translate($language);
            }
        );
        $di->setShared(
            'url',
            function() {
                $config_file = __DIR__ . '/../conf/system.ini';
                if (!file_exists($config_file)) {
                    return new Exception($config_file . ' not exists.');
                }
                $config = new Phalcon\Config\Adapter\Ini($config_file);
 
                $url = new Phalcon\Url();
                $url->setBaseUri($config->baseDir);
                return $url;
            }
        );
        $di->setShared(
            'view',
            function() {
                $view = new Phalcon\Mvc\View();
                $view->setViewsDir(self::APP_PATH . '/');
                $view->registerEngines(
                    [
                        '.volt' => 'volt',
                    ]
                );
                return $view;
            }
        );
        $di->setShared(
            'volt',
            function(ViewBaseInterface $view) use ($di) {
                $volt = new Phalcon\Mvc\View\Engine\Volt($view, $di);
                $volt->setOptions(
                    [
                        'always'    => true,
                        'extension' => '.php',
                        'separator' => '_',
                        'stat'      => true,
                        'path'      => self::APP_PATH . '/../cache/',
                    ]
                );
                return $volt;
            }
        );
        $this->setDI($di);
    }
    public function main()
    {
        $this->services();
        $this->loaders();
        $response = $this->handle($_GET['_url'] ?? '/');
        $response->send();
    }
}
