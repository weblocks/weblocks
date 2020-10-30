<?php
/**
 * (C) 2016 Weblocks project.
 * This software is released under the GPL, see LICENSE.
 * https://opensource.org/licenses/gpl-license.php
 */
declare(strict_types = 1);

class Application extends Phalcon\Mvc\Application
{
    const APP_PATH = __DIR__;

    public function services()
    {
        $di = new Phalcon\Di\FactoryDefault();
        $di->setShared(
            'view',
            function() {
                $view = new Phalcon\Mvc\View();
                $view->setViewsDir(self::APP_PATH . '/');
                return $view;
            }
        );
        $this->setDI($di);
    }
    public function loaders()
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
    public function main()
    {
        $this->services();
        $this->loaders();
        $response = $this->handle($_GET['_url'] ?? '/');
        $response->send();
    }
}
