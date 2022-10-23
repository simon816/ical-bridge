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

class ActionFactory
{
    private $actions;

    public function __construct()
    {
        $this->actions = [
            'display' => Action\DisplayAction::class,
            'frontpage' => Action\FrontpageAction::class,
        ];
    }

    /**
     * @param string $name The name of the action e.g. "Display", "List", or "Connectivity"
     */
    public function create(string $name): Action\ActionInterface
    {
        if (!isset($this->actions[$name])) {
            throw new \Exception("Unknown action: $name");
        }
        return new $this->actions[$name]();
    }
}
