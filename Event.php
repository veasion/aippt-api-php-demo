<?php

class Event
{
    const END_OF_LINE = "/\r\n|\n|\r/";

    private $data;
    private $eventType;
    private $id;

    public function __construct($data = '', $eventType = 'message', $id = null)
    {
        $this->data = $data;
        $this->eventType = $eventType;
        $this->id = $id;
    }

    public static function parse($raw)
    {
        $event = new static();
        $lines = preg_split(self::END_OF_LINE, $raw);

        foreach ($lines as $line) {
            $matched = preg_match('/(?P<name>[^:]*):?( ?(?P<value>.*))?/', $line, $matches);

            if (!$matched) {
                throw new InvalidArgumentException(sprintf('Invalid line %s', $line));
            }

            $name = $matches['name'];
            $value = $matches['value'];

            if ($name === '') {
                continue;
            }

            switch ($name) {
                case 'event':
                    $event->eventType = $value;
                    break;
                case 'data':
                    $event->data = empty($event->data) ? $value : "$event->data\n$value";
                    break;
                case 'id':
                    $event->id = $value;
                    break;
                default:
                    break;
            }
        }

        return $event;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getEventType()
    {
        return $this->eventType;
    }

    public function getId()
    {
        return $this->id;
    }

}