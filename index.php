<?php

require __DIR__ . '/vendor/autoload.php';

const PATH_LIB_BRIDGES = __DIR__ . '/bridges/';

/** Path to the cache folder */
const PATH_CACHE = __DIR__ . '/cache/';

/** Path to the whitelist file */
const WHITELIST = __DIR__ . '/whitelist.txt';

/** Path to the default whitelist file */
const WHITELIST_DEFAULT = __DIR__ . '/whitelist.default.txt';


$bridge = new ICalBridge\ICalBridge();
$bridge->main($argv ?? []);
