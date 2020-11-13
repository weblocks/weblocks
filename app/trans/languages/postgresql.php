<?php
$options = [
    'content' => [
        'EXIST USER' => "select usename from pg_user where usename = '{user}'",
        'CREATE USER' => "create user {user} with password '{password}'",
        'CREATE DATABASE' => "create database {database} with owner = {user} encoding = utf8",
        'GRANT ALL' => "",
    ],
];
