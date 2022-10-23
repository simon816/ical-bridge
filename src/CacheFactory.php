<?php

/**
 * This file is part of RSS-Bridge, a PHP project capable of generating RSS and
 * Atom feeds for websites that don't have one.
 *
 * For the full license information, please view the UNLICENSE file distributed
 * with this source code.
 *
 * @package Core
 * @license http://unlicense.org/ UNLICENSE
 * @link    https://github.com/rss-bridge/rss-bridge
 */

namespace ICalBridge;

class CacheFactory
{
    private $cacheNames;

    public function __construct()
    {
        $this->cacheNames = [
            'file' => FileCache::class,
        ];
    }

    /**
     * @param string|null $name The name of the cache e.g. "File", "Memcached" or "SQLite"
     */
    public function create(string $name = null): CacheInterface
    {
        $name ??= Configuration::getConfig('cache', 'type');

        if (!isset($this->cacheNames[$name])) {
            throw new \InvalidArgumentException('Cache name invalid!');
        }
        return new $this->cacheNames[$name]();
    }

    protected function sanitizeCacheName(string $name)
    {
        // Trim trailing '.php' if exists
        if (preg_match('/(.+)(?:\.php)/', $name, $matches)) {
            $name = $matches[1];
        }

        // Trim trailing 'Cache' if exists
        if (preg_match('/(.+)(?:Cache)$/i', $name, $matches)) {
            $name = $matches[1];
        }

        if (in_array(strtolower($name), array_map('strtolower', $this->cacheNames))) {
            $index = array_search(strtolower($name), array_map('strtolower', $this->cacheNames));
            return $this->cacheNames[$index];
        }
        return null;
    }
}
