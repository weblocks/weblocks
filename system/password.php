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
    $app = new Application();

    $err = $app->check();
    if ('' !== $err) {
        echo $err . PHP_EOL;
        return;
    }
    $app->services();
    $app->loaders();
    $app->load_config();

    while (true) {
        if (false == $app->input_database_connect_password()) {
            return;
        }
        $ans = $app->password_confirm();
        if ('y' == $ans) {
            break;
        }
        if ('q' == $ans) {
            return;
        }
    }
    $err = $app->change_password();
    if ('' != $err) {
        echo $err . PHP_EOL;
        return;
    }

    $err = $app->create_config();
    if ('' != $err) {
        echo $err . PHP_EOL;
        return;
    }
    $app->clear_screen();
    echo 'Success.' . PHP_EOL;
    echo PHP_EOL;
} catch (\Exception $e) {
    echo $e->getMessage();
}
