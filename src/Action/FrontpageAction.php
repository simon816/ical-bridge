<?php

namespace ICalBridge\Action;

final class FrontpageAction implements ActionInterface
{
    public function execute(array $request)
    {
        $showInactive = (bool) ($request['show_inactive'] ?? null);

        $totalBridges = 0;
        $totalActiveBridges = 0;

        $html = self::getHead()
            . self::getHeader()
            . self::getSearchbar()
            . self::getBridges($showInactive, $totalBridges, $totalActiveBridges)
            . self::getFooter($totalBridges, $totalActiveBridges, $showInactive);

        print $html;
    }

    private static function getHead()
    {
        return <<<EOD
<!DOCTYPE html><html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="description" content="iCal-Bridge" />
	<title>iCal-Bridge</title>
	<link href="static/style.css" rel="stylesheet">
	<link rel="icon" type="image/png" href="static/favicon.png">
	<script src="static/ical-bridge.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', icalbridge_toggle_bridge);
    </script>
</head>
<body onload="icalbridge_list_search()">
EOD;
    }

    private static function getHeader()
    {
        $warning = '';

        if (\ICalBridge\Debug::isEnabled()) {
            if (!ICalBridge\Debug::isSecure()) {
                $warning .= <<<EOD
<section class="critical-warning">Warning : Debug mode is active from any location,
 make sure only you can access iCal-Bridge.</section>
EOD;
            } else {
                $warning .= <<<EOD
<section class="warning">Warning : Debug mode is active from your IP address,
 your requests will bypass the cache.</section>
EOD;
            }
        }

        return <<<EOD
<header>
	<div class="logo"></div>
	{$warning}
</header>
EOD;
    }

    private static function getSearchbar()
    {
        $query = filter_input(INPUT_GET, 'q', \FILTER_SANITIZE_SPECIAL_CHARS);

        return <<<EOD
<section class="searchbar">
    <h3>Search</h3>
    <input
        type="text"
        name="searchfield"
        id="searchfield"
        placeholder="Insert URL or bridge name"
        onchange="icalbridge_list_search()"
        onkeyup="icalbridge_list_search()"
        value="{$query}"
    >
</section>
EOD;
    }

    private static function getBridges($showInactive, &$totalBridges, &$totalActiveBridges)
    {
        $body = '';
        $totalActiveBridges = 0;
        $inactiveBridges = '';

        $bridgeFactory = new \ICalBridge\BridgeFactory();
        $bridgeClassNames = $bridgeFactory->getBridgeClassNames();

        $totalBridges = count($bridgeClassNames);

        foreach ($bridgeClassNames as $bridgeClassName) {
            if ($bridgeFactory->isWhitelisted($bridgeClassName)) {
                $body .= \ICalBridge\BridgeCard::displayBridgeCard($bridgeClassName);
                $totalActiveBridges++;
            } elseif ($showInactive) {
                $inactiveBridges .= \ICalBridge\BridgeCard::displayBridgeCard($bridgeClassName, false) . PHP_EOL;
            }
        }

        $body .= $inactiveBridges;

        return $body;
    }

    private static function getFooter($totalBridges, $totalActiveBridges, $showInactive)
    {
        $version = \ICalBridge\Configuration::getVersion();

        $email = \ICalBridge\Configuration::getConfig('admin', 'email');
        $admininfo = '';
        if ($email) {
            $admininfo = <<<EOD
<br />
<span>
   You may email the administrator of this iCal-Bridge instance
   at <a href="mailto:{$email}">{$email}</a>
</span>
EOD;
        }

        $inactive = '';

        if ($totalActiveBridges !== $totalBridges) {
            if ($showInactive) {
                $inactive = '<a href="?show_inactive=0"><button class="small">Hide inactive bridges</button></a><br>';
            } else {
                $inactive = '<a href="?show_inactive=1"><button class="small">Show inactive bridges</button></a><br>';
            }
        }

        return <<<EOD
<section class="footer">
	<a href="https://github.com/simon816/ical-bridge">iCal-Bridge ~ GPL-3.0</a><br>
	<p class="version">{$version}</p>
	{$totalActiveBridges}/{$totalBridges} active bridges.<br>
	{$inactive}
	{$admininfo}
</section>
</body></html>
EOD;
    }
}
