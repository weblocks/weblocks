<?php
/**
 * (C) 2016 Weblocks project.
 * This software is released under the GPL, see LICENSE.
 * https://opensource.org/licenses/gpl-license.php
 */
try {
    if (!extension_loaded('phalcon')) {
        echo 'Phalcon not loaded.' . PHP_EOL;
        return;
    }
    require __DIR__ . '/Application.php';
    $application = new Application();
    $application->main();
} catch (\Exception $e) {
    echo $e->getMessage();
}
