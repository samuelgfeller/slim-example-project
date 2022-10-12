<?php

namespace App\Common;

use DateTimeZone;

class DateTimeImmutable extends \DateTimeImmutable implements \JsonSerializable
{

    public function __construct(string $datetime = 'now', ?DateTimeZone $timezone = null)
    {
        parent::__construct($datetime, $timezone);
    }

    /**
     * Output format for date time
     */
    public function jsonSerialize(): mixed
    {
        // Default SQL format to simplify testing
        if ($this->format('H:i:s') !== '00:00:00') {
            return $this->format('Y-m-d H:i:s');
        }
        // Without time if it has no content
        return $this->format('Y-m-d');
    }
}