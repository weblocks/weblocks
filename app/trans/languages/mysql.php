<?php
$options = [
    'content' => [
        'EXIST USER' => "select User from mysql.user where User = '{user}'",
        'CHANGE PASSWORD' => "ALTER USER '{user}'@'localhost' identified BY '{password}'",
        'CREATE USER' => "create user '{user}'@'localhost' identified by '{password}'",
        'CREATE DATABASE' => "create database {database} character set = utf8",
        'GRANT ALL' => "grant all on {database}.* to '{user}'@'localhost'",
    ],
];
