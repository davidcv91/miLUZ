<?php

namespace App\Entity;


class DateRange
{
    const DEFAULT_DATE_FORMAT = 'Y-m-d';

    private $datetimeStart;

    private $datetimeEnd;

    private $dateFormat;

    public function __construct(\DateTime $datetimeStart, \DateTime $datetimeEnd)
    {
        $this->datetimeStart = $datetimeStart;
        $this->datetimeEnd = $datetimeEnd;

        $this->dateFormat = self::DEFAULT_DATE_FORMAT;
    }

    public function getStartDatetime():\DateTime
    {
        return $this->datetimeStart;
    }

    public function getEndDatetime():\DateTime
    {
        return $this->datetimeEnd;
    }

    public function getStartDate():string
    {
        return $this->datetimeStart->format($this->dateFormat);
    }

    public function getEndDate():string
    {
        return $this->datetimeStart->format($this->dateFormat);
    }

    public function setFormat(string $format)
    {
        $this->dateFormat = $format;
    }
}