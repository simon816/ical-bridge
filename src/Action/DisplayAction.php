<?php

namespace ICalBridge\Action;

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

class DisplayAction implements ActionInterface
{
    public function execute(array $request)
    {
        $bridgeFactory = new \ICalBridge\BridgeFactory();

        $bridgeClassName = null;
        if (isset($request['bridge'])) {
            $bridgeClassName = $bridgeFactory->sanitizeBridgeName($request['bridge']);
        }

        if ($bridgeClassName === null) {
            throw new \InvalidArgumentException('Bridge name invalid!');
        }

        if (!$bridgeFactory->isWhitelisted($bridgeClassName)) {
            throw new \Exception('This bridge is not whitelisted');
        }

        $format = new \ICalBridge\ICalFormat();

        $bridge = $bridgeFactory->create($bridgeClassName);
        $bridge->loadConfiguration();

        $noproxy = array_key_exists('_noproxy', $request)
            && filter_var($request['_noproxy'], FILTER_VALIDATE_BOOLEAN);

        if (\ICalBridge\Configuration::getConfig('proxy', 'url') && \ICalBridge\Configuration::getConfig('proxy', 'by_bridge') && $noproxy) {
            define('NOPROXY', true);
        }

        if (array_key_exists('_cache_timeout', $request)) {
            if (!\ICalBridge\Configuration::getConfig('cache', 'custom_timeout')) {
                unset($request['_cache_timeout']);
                $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '?' . http_build_query($request);
                header('Location: ' . $uri, true, 301);
                return;
            }

            $cache_timeout = filter_var($request['_cache_timeout'], FILTER_VALIDATE_INT);
        } else {
            $cache_timeout = $bridge->getCacheTimeout();
        }

        // Remove parameters that don't concern bridges
        $bridge_params = array_diff_key(
            $request,
            array_fill_keys(
                [
                    'action',
                    'bridge',
                    'format',
                    '_noproxy',
                    '_cache_timeout',
                    '_error_time'
                ],
                ''
            )
        );

        // Remove parameters that don't concern caches
        $cache_params = array_diff_key(
            $request,
            array_fill_keys(
                [
                    'action',
                    'format',
                    '_noproxy',
                    '_cache_timeout',
                    '_error_time'
                ],
                ''
            )
        );

        $cacheFactory = new \ICalBridge\CacheFactory();

        $cache = $cacheFactory->create();
        $cache->setScope('');
        $cache->purgeCache(86400); // 24 hours
        $cache->setKey($cache_params);

        $events = [];
        $infos = [];
        $mtime = $cache->getTime();

        if (
            $mtime !== false
            && (time() - $cache_timeout < $mtime)
            && !\ICalBridge\Debug::isEnabled()
        ) {
            // Load cached data
            // Send "Not Modified" response if client supports it
            // Implementation based on https://stackoverflow.com/a/10847262
            if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
                $stime = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);

                if ($mtime <= $stime) {
                    // Cached data is older or same
                    header('Last-Modified: ' . gmdate('D, d M Y H:i:s ', $mtime) . 'GMT', true, 304);
                    return;
                }
            }

            $cached = $cache->loadData();

            if (isset($cached['events']) && is_array($cached['extraInfos'])) {
                foreach ($cached['events'] as $event) {
                    $events[] = new \ICalBridge\CalEvent($event);
                }
                $infos = $cached['extraInfos'];
            }
        } else {
            // Collect new data
            try {
                $bridge->setDatas($bridge_params);
                $bridge->collectData();

                $events = $bridge->getEvents();

                if (isset($events[0]) && is_array($events[0])) {
                    $newEvents = [];
                    foreach ($events as $event) {
                        $newEvents[] = new \ICalBridge\CalEvent($event);
                    }
                    $events = $newEvents;
                }

                $infos = [
                    'name' => $bridge->getName(),
                    'uri'  => $bridge->getURI(),
                    'donationUri'  => $bridge->getDonationURI(),
                    'icon' => $bridge->getIcon()
                ];
            } catch (\Throwable $e) {
                Logger::error(sprintf('Exception in %s', $bridgeClassName), ['e' => $e]);
                $errorCount = logBridgeError($bridge::NAME, $e->getCode());

                if ($errorCount >= \ICalBridge\Configuration::getConfig('error', 'report_limit')) {
                    throw $e;
                }
            }

            $cache->saveData([
                'events' => array_map(function (\ICalBridge\CalEvent $event) {
                    return $event->toArray();
                }, $events),
                'extraInfos' => $infos
            ]);
        }

        $format->setEvents($events);
        $format->setExtraInfos($infos);
        $lastModified = $cache->getTime();
        $format->setLastModified($lastModified);
        if ($lastModified) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s ', $lastModified) . 'GMT');
        }
        header('Content-Type: ' . $format->getMimeType() . '; charset=' . $format->getCharset());
        print $format->stringify();
    }
}
