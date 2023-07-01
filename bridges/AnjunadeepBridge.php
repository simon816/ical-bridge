<?php

class AnjunadeepBridge extends ICalBridge\BridgeAbstract
{
    const NAME = 'Anjunadeep Events';
    const URI = 'https://anjunadeep.com/events';
    const DESCRIPTION = 'Events on the Anjunadeep website';
    const MAINTAINER = 'simon816';
    const PARAMETERS = [
        '' => [
            'location' => [
                'name' => 'Location',
                'type' => 'text',
                'exampleValue' => 'United Kingdom',
            ],
        ],
    ];

    public function collectData()
    {
        $this->events = [];

        $html = getSimpleHTMLDOM($this->getURI());
        $elements = $html->find('main article div.full-block > div');
        foreach ($elements as $elem) {
            $timeElem = $elem->find('time', 0);
            if ($timeElem === null) {
                continue;
            }
            $summaryElem = $elem->find('h4', 0);
            $linkElem = $elem->find('a', 0);
            $text = $elem->find('div.font-light');
            list($locationElem, $detailsElem) = $text;

            $location = html_entity_decode($locationElem->text());
            if ($loc_filter = $this->getInput('location')) {
                if (mb_stripos($location, $loc_filter) === false) {
                    continue;
                }
            }

            $start = strtotime($timeElem->getAttribute('datetime'));
            $link = html_entity_decode($linkElem->getAttribute('href'));
            $this->events[] = [
                'summary' => html_entity_decode($summaryElem->text()),
                'description' => html_entity_decode($detailsElem->text()) . "\n\nTickets: $link",
                'location' => $location,
                'startTime' => $start,
                'endTime' => strtotime('+2 hours', $start),
            ];
        }
    }
}
