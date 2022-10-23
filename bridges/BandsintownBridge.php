<?php

class BandsintownBridge extends ICalBridge\BridgeAbstract
{
    const NAME = 'Bandsintown Events';
    const URI = 'https://www.bandsintown.com/';
    const DESCRIPTION = 'Events from artists on bandsintown.com';
    const MAINTAINER = 'simon816';
    const PARAMETERS = [
        '' => [
            'artist_id' => [
                'name' => 'Artist ID',
                'type' => 'number',
                'exampleValue' => '100000',
                'required' => true,
            ]
        ]
    ];

    private $name = null;

    public function getName()
    {
        return $this->name ?? self::NAME;
    }

    public function collectData()
    {
        $a_id = $this->getInput('artist_id');
        $url = self::URI . "a/$a_id";

        $html = getSimpleHTMLDOMCached($url);
        $json = null;
        foreach ($html->find('script') as $scriptElem) {
            $script = $scriptElem->innertext;
            if (str_starts_with($script, 'window.__data=')) {
                $json = \Json::decode(substr($script, strlen('window.__data=')));
                break;
            }
        }
        if ($json === null) {
            throw new \Exception("Could not find window.__data");
        }
        $this->name = $json['title'];
        $events = $json['artistView']['body']['events']['upcomingEvents']['events'];
        foreach ($events as $event) {
            $start = strtotime($event['streamStart']);
            # just fudge +2 hours if it is not known
            $end = $event['streamEnd'] ? strtotime($event['streamEnd']) : strtotime('+2 hours', $start);
            $this->events[] = [
                # TODO timezone
                'summary' => $event['title'],
                'description' => $event['eventUrl'],
                'location' => $event['venueName'] . "\n" . $event['location'],
                'startTime' => $start,
                'endTime' => $end,
            ];
        }
    }
}
