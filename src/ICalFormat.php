<?php

namespace ICalBridge;

class ICalFormat
{
    /** The default charset (UTF-8) */
    const DEFAULT_CHARSET = 'UTF-8';

    const MIME_TYPE = 'text/calendar';

    /** @var string $charset The charset */
    protected $charset;

    /**
     * @var int $lastModified A timestamp to indicate the last modified time of
     * the output data.
     */
    protected $lastModified;

    private $name;
    private $events = [];

    public function setEvents(array $events)
    {
        $this->events = $events;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function setExtraInfos(array $infos)
    {
        $this->name = $infos['name'];
    }

    public function getMimeType()
    {
        return static::MIME_TYPE;
    }

    public function getCharset()
    {
        $charset = $this->charset;

        if (is_null($charset)) {
            return static::DEFAULT_CHARSET;
        }
        return $charset;
    }

    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Set the last modified time
     *
     * @param int $lastModified The last modified time
     * @return void
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    }


    public function stringify()
    {
        $cal = new \ZCiCal();
        if ($this->name) {
            $this->addProp($cal, 'X-WR-CALNAME', $this->name);
        }
        foreach ($this->events as $event) {
            $eventobj = new \ZCiCalNode("VEVENT", $cal->curnode);
            if ($val = $event->summary()) {
                $this->addText($eventobj, 'SUMMARY', $val);
            }
            if ($val = $event->startTime()) {
                $this->addDT($eventobj, 'DTSTART', $val);
            }
            if ($val = $event->endTime()) {
                $this->addDT($eventobj, 'DTEND', $val);
            }
            if ($val = $event->uid()) {
                $this->addText($eventobj, 'UID', $val);
            }
            $this->addDT($eventobj, 'DTSTAMP', null);
            if ($val = $event->description()) {
                $this->addText($eventobj, 'DESCRIPTION', $val);
            }
            if ($val = $event->location()) {
                $this->addText($eventobj, 'LOCATION', $val);
            }
        }
        return $cal->export();
    }

    private function addText(\ZCiCalNode $event, string $label, string $value)
    {
        $event->addNode(new \ZCiCalDataNode($label . ":" . \ZCiCal::formatContent($value)));
    }

    private function addDT(\ZCiCalNode $event, string $label, int $value = null)
    {
        $event->addNode(new \ZCiCalDataNode($label . ":" . \ZDateHelper::toiCalDateTime($value)));
    }

    private function addProp(\ZCiCal $cal, string $label, string $value)
    {
        $node = new \ZCiCalDataNode($label . ":" . \ZCiCal::formatContent($value));
        $cal->curnode->data[$node->getName()] = $node;
    }
}
