<?php

namespace Utils;

use DateTime;


class DateUtils
{
    private array $formats;
//    private array $formatModifications;

    // these date arrays will hold both current and previous periods, as DateTime objects
    private array $weeklyDates;
    private array $monthlyDates;

    public function __construct()
    {
        // todo: add DB query format(s)
        $this->formats = array(
            'aa' => 'Y-m-d\TH:i:s.v',
            'aa-results' => 'M j, Y',
            'gsc' => 'Y-m-d',
            'at' => 'Y-m-d\TH:i:s.v',
            'header' => 'M d',
        );


        $this->initDates();
    }

    public function initDates() {
        if ($this->todayIsSunday()) {
            $weekStart = new DateTime('last sunday midnight');
            $weekEnd = new DateTime('yesterday 11:59 pm');
        } else {
            $weekStart = new DateTime('-2 sunday midnight');
            $weekEnd = new DateTime('last saturday 11:59 pm');
        }

        $this->weeklyDates['current'] = array('start' => $weekStart, 'end' => $weekEnd);
        $this->weeklyDates['previous'] = array(
            'start' => (clone $weekStart)->modify("-1 week"),
            'end' => (clone $weekEnd)->modify("-1 week")
        );

        $monthStart = new DateTime("first day of last month midnight");
        $monthEnd = new DateTime("last day of last month 11:59 pm");

        $this->monthlyDates['current'] = array('start' => $monthStart, 'end' => $monthEnd);
        $this->monthlyDates['previous'] = array(
            'start' => (clone $monthStart)->modify("-1 month"),
            'end' => (clone $monthEnd)->modify("-1 month")
        );
    }

    public function getWeeklyDates($format = ''): array
    {
        if ($format === '') {
            return $this->weeklyDates;
        }

        return array(
            'current' => array_map(fn($date) => $this->toFormat($date, $format), $this->weeklyDates['current']),
            'previous' => array_map(fn($date) => $this->toFormat($date, $format), $this->weeklyDates['previous']),
        );
    }

    public function getMonthlyDates($format = ''): array
    {
        if ($format === '') {
            return $this->monthlyDates;
        }

        return array(
            'current' => array_map(fn($date) => $this->toFormat($date, $format), $this->monthlyDates['current']),
            'previous' => array_map(fn($date) => $this->toFormat($date, $format), $this->monthlyDates['previous']),
        );
    }

    public function toFormat(DateTime $date, string $formatName = 'gsc'): string
    {
        // default to gsc format if invalid format given
        if (!array_key_exists($formatName, $this->formats)) {
            $formatName = 'gsc';
        }

        // todo: if the format has an associated modification function, call it before formatting
//        if (array_key_exists($formatName, $this->formatModifications)) {
//            $date = $this->formatModifications[$formatName]($date);
//        }

        return $date->format($this->formats[$formatName]);
    }

    public function todayIsSunday(): bool {
        return getdate(time())['wday'] === 0;
    }
}