<?php

namespace App\Service;


use App\Entity\ConsumptionCollection;

class ConsumptionStatsHelper
{

    public function getTotalConsumption(ConsumptionCollection $consumptionCollection)
    {
        $result = 0;

        foreach ($consumptionCollection->getIterator() as $consumption) {
            $result += $consumption->getConsumptionKWh();
        }

        return $result;
    }

    public function getAggregatedConsumptionByDayOfWeek(ConsumptionCollection $consumptionCollection)
    {
        $result = array_fill_keys(range(1, 7), 0);

        foreach ($consumptionCollection->getIterator() as $consumption) {
            $result[$consumption->getDayOfWeek()] += $consumption->getConsumptionKWh();
        }

        return $result;
    }

    public function getAggregatedConsumptionByDate(ConsumptionCollection $consumptionCollection)
    {
        $result = [];

        foreach ($consumptionCollection->getIterator() as $consumption) {
            if (!isset($result[$consumption->getDate()])) {
                $result[$consumption->getDate()] = 0;
            }

            $result[$consumption->getDate()] += $consumption->getConsumptionKWh();
        }

        return $result;
    }

    public function getAggregatedConsumptionByHour(ConsumptionCollection $consumptionCollection)
    {
        $result = array_fill_keys(range(0, 23), 0);

        foreach ($consumptionCollection->getIterator() as $consumption) {
            $result[$consumption->getHour()] += $consumption->getConsumption();
        }

        return $result;
    }

    public function findTwoConsecutiveHoursWithGreaterConsumption(array $aggregatedConsumptionHourSorted)
    {
        $aggregatedConsumptionHourSorted = array_keys($aggregatedConsumptionHourSorted);

        for($i = 0; $i < count($aggregatedConsumptionHourSorted)-1; ++$i) {
            $currentValue = $aggregatedConsumptionHourSorted[$i];
            $nextValue = $aggregatedConsumptionHourSorted[$i+1];

            if ($nextValue == $currentValue+1 or $nextValue == $currentValue-1) {
                return [$currentValue, $nextValue];
            }
        }

        return [];
    }

    public function consumptionDuring50HoursWithGreaterConsumption(ConsumptionCollection $consumptionCollection)
    {
        $consumptionCollectionArray = $consumptionCollection->toArray();

        usort($consumptionCollectionArray, function($a, $b) {
            return $b->getConsumption() - $a->getConsumption();
        });

        $consumptionCollection = array_slice($consumptionCollectionArray, 0, 50);

        $result = 0;
        foreach ($consumptionCollection as $consumption) {
            $result += $consumption->getConsumptionKWh();
        }
        return $result;
    }

    /*
     * Tempo Solar Hours
     * Summer: 18h - 09h
     * Winter: 17h - 10h
     * +info: https://www.endesaclientes.com/tempo-solar.html
     */
    public function consumptionInTempoSolar(ConsumptionCollection $consumptionCollection)
    {
        $result = 0;

        foreach ($consumptionCollection->getIterator() as $consumption) {
            if ($consumption->isInTempoSolar())   {
                $result += $consumption->getConsumptionKWh();
            }
        }

        return $result;
    }

}