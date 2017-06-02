<?php
/**
 * site-meta config file
 * @package site-meta
 * @version 0.0.1
 * @upgrade true
 */

return [
    '__name' => 'site-meta',
    '__version' => '0.0.1',
    '__git' => 'https://github.com/getphun/site-meta',
    '__files' => [
        'modules/site-meta' => [
            'install',
            'remove',
            'update'
        ]
    ],
    '__dependencies' => [
        '\\site-param'
    ],
    '_services' => [
        'meta' => 'SiteMeta\\Service\\Meta'
    ],
    '_autoload' => [
        'classes' => [
            'SiteMeta\\Service\\Meta' => 'modules/site-meta/service/Meta.php'
        ],
        'files' => []
    ]
];