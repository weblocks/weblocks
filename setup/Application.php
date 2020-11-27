<?php
/**
 * (C) 2016 Weblocks project.
 * This software is released under the GPL, see LICENSE.
 * https://opensource.org/licenses/gpl-license.php
 */
declare(strict_types = 1);

use Phalcon\Mvc\ViewBaseInterface;

define('LINUX',   'linux');
define('WINDOWS', 'windows');

class Application extends Phalcon\Mvc\Application
{
    const APP_PATH = __DIR__ . '/../app';
    const CONFIG_FILE = __DIR__ . '/../conf/database.ini'; 
    const PHALCON_VERSION = 4;
    const SCHEMA_FILE = __DIR__ . '/schema.php';

    private $_adapters = [];
    private $_config = [];
    private $_database_admin = [];
    private $_os = LINUX;
    private $_pdo;
    private $_trans;
    private $_weblocks_admin = [];

    private function check()
    {
        $err = '';

        if (!$err) {
            if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
                $this->_os = WINDOWS;
            }
        }

        if (!$err) {
            if (file_exists(self::CONFIG_FILE)) {
                $err = 'Already exists ' . self::CONFIG_FILE . '.';
            }
        }

        if (!$err) {
            $ver = Phalcon\Version::getPart(Phalcon\Version::VERSION_MAJOR);
            if (self::PHALCON_VERSION > $ver) {
                $err = 'Weblocks needs Phalcon ' . self::PHALCON_VERSION . ' or late.';
            }
        }

        if (!$err) {
            $path = session_save_path();
            if ('' === $path) {
                $err = 'Please set session.save_path';
            }
        }

        if (!$err) {
            if (extension_loaded('mysqli')) {
                if (extension_loaded('pdo_mysql')) {
                    $this->_adapters['MySQL'] = 'mysql';
                }
            }
            if (extension_loaded('pgsql')) {
                if (extension_loaded('pdo_pgsql')) {
                    $this->_adapters['PostgreSQL'] = 'postgresql';
                }
            }
/*
            if (extension_loaded('sqlite3')) {
                if (extension_loaded('pdo_sqlite')) {
                    $this->_adapters['SQLite3'] = 'sqlite';
                }
            }
*/
            if (!count($this->_adapters)) {
                $err = 'database adapter not loaded.';
            }
        }
        return $err;
    }
    private function clear_screen()
    {
        $cmd = 'clear';
        if (WINDOWS === $this->_os) {
            $cmd = 'cls';
        }
        system($cmd);
    }
    private function input()
    {
        $line = trim(fgets(STDIN), PHP_EOL);
        return $line;
    }
    private function input_adapter()
    {
        if (!isset($this->_config['adapter'])) {
            $key = array_key_first($this->_adapters);
            $this->_config['adapter'] = $this->_adapters[$key];
        }
        while(true) {
            $this->clear_screen();
            $this->title('Select DBMS');

            $num = 1;
            foreach ($this->_adapters as $key => $value) {
                echo $num++ . ') ' . $key . PHP_EOL;
            }
            echo PHP_EOL;

            $keys = array_keys($this->_adapters, $this->_config['adapter']);
            echo 'blank is ' . $keys[0] . PHP_EOL;
            echo 'Select number (if Q or q then exit) : ';

            $num = $this->input();
            if ('Q' === $num || 'q' === $num) {
                return false;
            }
            if ('' === $num) {
                return true;
            }
            if ($num >= 1 && $num <= count($this->_adapters)) {
                $bak_adapter = $this->_config['adapter'];
                $adapters = array_values($this->_adapters);
                $this->_config['adapter'] = $adapters[$num - 1];
                if ($this->_config['adapter'] !== $bak_adapter) {
                    unset($this->_database_admin['name']);
                    unset($this->_database_admin['pass']);
                }
                return true;
            }
        }
    }
    private function input_host()
    {
        if (!isset($this->_config['host'])) {
            $this->_config['host'] = 'localhost';
        }
        while (true) {
            $this->clear_screen();
            $this->title('Host name');

            echo 'blank is ' . $this->_config['host'] . PHP_EOL;
            echo 'Host name (if Q or q then exit) : ';
            $host = $this->input();
            if ('Q' === $host || 'q' === $host) {
                return false;
            }
            if ('' === $host) {
                return true;
            }
            if ('' !== $host) {
                $this->_config['host'] = $host;
                return true;
            }
        }
    }
    private function input_database_name()
    {
        if (!isset($this->_config['dbname'])) {
            $this->_config['dbname'] = 'weblocks';
        }
        while (true) {
            $this->clear_screen();
            $this->title('Database name');

            echo 'blank is ' . $this->_config['dbname'] . PHP_EOL;
            echo 'Database name (if Q or q then exit) : ';
            $name = $this->input();
            if ('Q' === $name || 'q' === $name) {
                return false;
            }
            if ('' === $name) {
                return true;
            }
            if ('' !== $name) {
                $this->_config['dbname'] = $name;
                return true;
            }
        }
    }
    private function input_database_admin()
    {
        $adapter = '';
        foreach ($this->_adapters as $key => $value) {
            if ($value === $this->_config['adapter']) {
                $adapter = $key;
            }
        }
        if (!isset($this->_database_admin['name'])) {
            if ('mysql' === $this->_config['adapter']) {
                $this->_database_admin['name'] = 'root';
            }
            if ('postgresql' === $this->_config['adapter']) {
                $this->_database_admin['name'] = 'postgres';
            }
        }
        while (true) {
            $this->clear_screen();
            $this->title($adapter . ' administrator');

            echo 'blank is ' . $this->_database_admin['name'] . PHP_EOL;
            echo $adapter . ' admin (if Q or q then exit) : ';
            $name = $this->input();
            if ('Q' === $name || 'q' === $name) {
                return false;
            }
            if ('' === $name) {
                break;
            }
            if ('' !== $name) {
                $this->_database_admin['name'] = $name;
                break;
            }
        }
        while (true) {
            $this->clear_screen();
            $this->title($adapter . ' administrator');

            echo $adapter . ' admin (if Q or q then exit) : ' . $this->_database_admin['name'] . PHP_EOL;
            echo PHP_EOL;

            if (isset($this->_database_admin['pass'])) {
                echo 'blank is ' . $this->_database_admin['pass'] . PHP_EOL;
            }
            echo 'Password (if Q or q then exit) : ';
            $pass = $this->input();
            if ('Q' === $pass || 'q' === $pass) {
                return false;
            }
            if (isset($this->_database_admin['pass']) && '' === $pass) {
                break;
            }
            if ('' !== $pass) {
                $this->_database_admin['pass'] = $pass;
                break;
            }
        }
        return true;
    }
    private function input_database_connect_user()
    {
        if (!isset($this->_config['username'])) {
            $this->_config['username'] = 'dbuser';
        }
        while (true) {
            $this->clear_screen();
            $this->title($this->_config['dbname'] . ' connect user');

            echo 'blank is ' . $this->_config['username'] . PHP_EOL;
            echo $this->_config['dbname'] . ' connect user (if Q or q then exit) : ';
            $name = $this->input();
            if ('Q' === $name || 'q' === $name) {
                return false;
            }
            if ('' === $name) {
                break;
            }
            if ('' !== $name) {
                $this->_config['username'] = $name;
                break;
            }
        }
        while (true) {
            $this->clear_screen();
            $this->title($this->_config['dbname'] . ' connect user');

            echo $this->_config['dbname'] . ' connect user (if Q or q then exit) : ' . $this->_config['username'] . PHP_EOL;
            echo PHP_EOL;

            if (isset($this->_config['password'])) {
                echo 'blank is ' . $this->_config['password'] . PHP_EOL;
            }
            echo 'Password (if Q or q then exit) : ';
            $pass = $this->input();
            if ('Q' === $pass || 'q' === $pass) {
                return false;
            }
            if (isset($this->_config['password']) && '' === $pass) {
                break;
            }
            if ('' !== $pass) {
                $this->_config['password'] = $pass;
                break;
            }
        }
        return true;
    }
    private function input_weblocks_admin()
    {
        if (!isset($this->_weblocks_admin['name'])) {
            $this->_weblocks_admin['name'] = 'weblocker';
        }
        while (true) {
            $this->clear_screen();
            $this->title('Weblocks administrator');

            echo 'blank is ' . $this->_weblocks_admin['name'] . PHP_EOL;
            echo 'Weblocks admin (if Q or q then exit) : ';
            $name = $this->input();
            if ('Q' === $name || 'q' === $name) {
                return false;
            }
            if ('' === $name) {
                break;
            }
            if ('' !== $name) {
                $this->_weblocks_admin['name'] = $name;
                break;
            }
        }
        while (true) {
            $this->clear_screen();
            $this->title('Weblocks administrator');

            echo 'Weblocks admin (if Q or q then exit) : ' . $this->_weblocks_admin['name'] . PHP_EOL;
            echo PHP_EOL;

            if (isset($this->_weblocks_admin['pass'])) {
                echo 'blank is ' . $this->_weblocks_admin['pass'] . PHP_EOL;
            }
            echo 'Password (if Q or q then exit) : ';
            $pass = $this->input();
             if ('Q' === $pass || 'q' === $pass) {
                return false;
            }
            if (isset($this->_weblocks_admin['pass']) && '' === $pass) {
                break;
            }
            if ('' !== $pass) {
                $this->_weblocks_admin['pass'] = $pass;
                break;
            }
        }
        return true;
    }
    private function input_confirm()
    {
        while (true) {
            $this->clear_screen();
            $this->title('Setup configuration confirm');

            $keys = array_keys($this->_adapters, $this->_config['adapter']);
            echo sprintf('%19s', 'Adapter = ') . $keys[0] . PHP_EOL;
            echo sprintf('%19s', 'Host = ') . $this->_config['host'] . PHP_EOL;
            echo sprintf('%19s', 'Database Name = ') . $this->_config['dbname'] . PHP_EOL;
            echo sprintf('%19s', $keys[0] . ' Admin = ') . $this->_database_admin['name'] . PHP_EOL;
            echo sprintf('%19s', 'Password  = ') . $this->_database_admin['pass'] . PHP_EOL;
            echo PHP_EOL;
            echo sprintf('%19s', 'Connect User = ') . $this->_config['username'] . PHP_EOL;
            echo sprintf('%19s', 'Password = ') . $this->_config['password'] . PHP_EOL;
            echo PHP_EOL;
            echo sprintf('%19s', 'Weblocks Admin = ') . $this->_weblocks_admin['name'] . PHP_EOL;
            echo sprintf('%19s', 'Password = ') . $this->_weblocks_admin['pass'] . PHP_EOL;
            echo PHP_EOL;
            echo 'OK ? [y / n] or Quit ? [Q / q] : ';
            $ans = $this->input();
            if ('Y' === $ans || 'y' === $ans) {
                return 'y';
            }
            if ('N' === $ans || 'n' === $ans) {
                return 'n';
            }
            if ('Q' === $ans || 'q' === $ans) {
                return 'q';
            }
        }
    }
    private function create_config()
    {
        $fp = fopen(self::CONFIG_FILE, 'w');
        if (false === $fp) {
            return "Can't create config file.";
        }
        fwrite($fp, 'adapter  = ' . $this->_config['adapter'] . PHP_EOL);
        fwrite($fp, '[options]' . PHP_EOL);
        fwrite($fp, 'host     = ' . $this->_config['host'] . PHP_EOL);
        fwrite($fp, 'username = ' . $this->_config['username'] . PHP_EOL);
        fwrite($fp, 'password = ' . $this->_config['password'] . PHP_EOL);
        fwrite($fp, 'dbname   = ' . $this->_config['dbname'] . PHP_EOL);
        fclose($fp);

        return '';
    }
    private function create_database()
    {
        $config['adapter']             = $this->_config['adapter'];
        $config['options']['host']     = $this->_config['host'];
        $config['options']['username'] = $this->_database_admin['name'];
        $config['options']['password'] = $this->_database_admin['pass'];

        $adapter = new Phalcon\Db\Adapter\PdoFactory();
        $this->_pdo = $adapter->load($config);
        if (false === $this->_pdo->connect()) {
            return 'Connect error.';
        }

        $this->_trans = new Translate($this->_config['adapter']);

        $sql = $this->_trans->from('EXIST USER');
        $sql = str_replace('{user}', $this->_config['username'], $sql);
        $result = $this->_pdo->query($sql);
        if (false === $result) {
            return 'Error : ' . $sql;
        }
        if (!count($result->fetchAll())) {
            $sql = $this->_trans->from('CREATE USER');
            $sql = str_replace('{user}', $this->_config['username'], $sql);
            $sql = str_replace('{password}', $this->_config['password'], $sql);
            if (false === $this->_pdo->execute($sql)) {
                return 'Error : ' . $sql;
            }
        }

        $sql = $this->_trans->from('CREATE DATABASE');
        $sql = str_replace('{database}', $this->_config['dbname'], $sql);
        $sql = str_replace('{user}', $this->_config['username'], $sql);
        if (false === $this->_pdo->execute($sql)) {
            return 'Error : ' . $sql;
        }

        $sql = $this->_trans->from('GRANT ALL');
        if ('' !== $sql) {
            $sql = str_replace('{database}', $this->_config['dbname'], $sql);
            $sql = str_replace('{user}', $this->_config['username'], $sql);
            if (false === $this->_pdo->execute($sql)) {
                return 'Error : ' . $sql;
            }
        }

        if (false === $this->_pdo->close()) {
            return "Can't close connect.";
        }
        return '';
    }
    private function create_tables()
    {
        if (!is_readable(self::SCHEMA_FILE)) {
            return 'Not exist schema file.';
        }
        $factory = new Phalcon\Config\ConfigFactory();
        $options = $factory->load(self::CONFIG_FILE);
        $adapter = new Phalcon\Db\Adapter\PdoFactory();
        $this->_pdo = $adapter->load($options);

        $schema = new Phalcon\Config\Adapter\Php(self::SCHEMA_FILE);

        $name = $schema->name;
        if (!$this->_pdo->tableExists($name)) {
            $this->_pdo->createTable($name, '', $schema->table->toArray());
        }
        foreach ($schema->data->values as $values) {
            $this->_pdo->insert($name, $values->toArray(), $schema->data->fields->toArray());
        }
        foreach ($schema->views as $value) {
            $view = $value->toArray();
            $this->_pdo->createView($view['name'], $view);
        }
        if (false === $this->_pdo->close()) {
            return "Can't close connect.";
        }
        return '';
    }
    private function save_weblocks_admin()
    {
        $user = new Users();
        $user->username = $this->_weblocks_admin['name'];
        $user->password = $this->_weblocks_admin['pass'];
        $user->role     = 1;  // 1 = administrator
        if (!$user->save()) {
            return "Can't save " . $this->_weblocks_admin['name'] . '.';
        }
        return '';
    }
    private function loaders()
    {
        $paths = [];
        $files = scandir(self::APP_PATH);
        foreach ($files as $file) {
            if ('.' === $file || '..' === $file) {
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
        $di->set(
            'trans',
            function($language) {
                return new Translate($language);
            }
        );
        $this->setDI($di);
    }
    private function title($title)
    {
        $len = strlen($title) + 4;
        echo str_repeat('=', $len) . PHP_EOL;
        echo '= ' . $title . ' ='  . PHP_EOL;
        echo str_repeat('=', $len) . PHP_EOL;
        echo PHP_EOL;
    }
    public function main()
    {
        $err = $this->check();
        if ('' !== $err) {
            echo $err . PHP_EOL;
            return;
        }
        $this->services();
        $this->loaders();

        while (true) {
            if (false == $this->input_adapter()) {
                return;
            }
            if (false == $this->input_host()) {
                return;
            }
            if (false == $this->input_database_name()) {
                return;
            }
            if (false == $this->input_database_admin()) {
                return;
            }
            if (false == $this->input_database_connect_user()) {
                return;
            }
            if (false == $this->input_weblocks_admin()) {
                return;
            }
            $ans = $this->input_confirm();
            if ('y' == $ans) {
                break;
            }
            if ('q' == $ans) {
                return;
            }
        }
        $err = $this->create_config();
        if ('' != $err) {
            echo $err . PHP_EOL;
            return;
        }
        $err = $this->create_database();
        if ('' != $err) {
            echo $err . PHP_EOL;
            return;
        }
        $err = $this->create_tables();
        if ('' != $err) {
            echo $err . PHP_EOL;
            return;
        }
        $err = $this->save_weblocks_admin();
        if ('' != $err) {
            echo $err . PHP_EOL;
            return;
        }
        $this->clear_screen();
        echo 'Congratulations! Weblocks installed.' . PHP_EOL;
        echo PHP_EOL;
    }
}
