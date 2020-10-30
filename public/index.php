<?php
/**
 * (C) 2016 Weblocks project.
 * This software is released under the GPL, see LICENSE.
 * https://opensource.org/licenses/gpl-license.php
 */
try {
    require __DIR__ . '/../app/Application.php';
    $application = new Application();
    $application->main();
} catch (\Exception $e) {
    echo $e->getMessage();
}
