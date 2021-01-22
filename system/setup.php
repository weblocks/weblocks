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

    $err = $app->setup_check();
    if ('' !== $err) {
        echo $err . PHP_EOL;
        return;
    }
    $app->services();
    $app->loaders();

    while (true) {
        if (false == $app->input_adapter()) {
            return;
        }
        if (false == $app->input_host()) {
            return;
        }
        if (false == $app->input_database_name()) {
            return;
        }
        if (false == $app->input_database_admin()) {
            return;
        }
        if (false == $app->input_database_connect_user()) {
            return;
        }
        if (false == $app->input_weblocks_admin()) {
            return;
        }
        $ans = $app->setup_confirm();
        if ('y' == $ans) {
            break;
        }
        if ('q' == $ans) {
            return;
        }
    }
    $err = $app->create_config();
    if ('' != $err) {
        echo $err . PHP_EOL;
        return;
    }
    $err = $app->create_database();
    if ('' != $err) {
        echo $err . PHP_EOL;
        return;
    }
    $err = $app->create_tables();
    if ('' != $err) {
        echo $err . PHP_EOL;
        return;
    }
    $err = $app->save_weblocks_admin();
    if ('' != $err) {
        echo $err . PHP_EOL;
        return;
    }
    $app->clear_screen();
    echo 'Congratulations! Weblocks installed.' . PHP_EOL;
    echo PHP_EOL;
} catch (\Exception $e) {
    echo $e->getMessage();
}
