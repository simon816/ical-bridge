<?php

namespace ICalBridge;

class CalEvent
{

    protected $startTime = null;
    protected $endTime = null;
    protected $summary = null;
    protected $description = null;
    protected $location = null;
    protected $uid = null;

    public function __construct(array $event)
    {
        foreach ($event as $key => $value) {
            $this->$key = $value;
        }
    }

    public function startTime()
    {
        return $this->startTime;
    }

    public function endTime()
    {
        return $this->endTime;
    }

    public function summary()
    {
        return $this->summary;
    }

    public function description()
    {
        return $this->description;
    }

    public function location()
    {
        return $this->location;
    }

    public function uid()
    {
        return $this->uid;
    }

    /**
     * Transform current object to array
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            [
                'startTime' => $this->startTime,
                'endTime' => $this->endTime,
                'summary' => $this->summary,
                'description' => $this->description,
                'location' => $this->location,
                'uid' => $this->uid,
            ],
            // XXX: $this->misc
        );
    }
}
