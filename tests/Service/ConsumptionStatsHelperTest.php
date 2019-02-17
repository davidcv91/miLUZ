<?php

namespace App\Tests\Service;

use App\Entity\Consumption;
use App\Entity\ConsumptionCollection;
use App\Service\ConsumptionStatsHelper;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConsumptionStatsHelperTest extends WebTestCase
{

    private $consumptionStatsHelper;
    private $consumptionCollection;

    public function __construct()
    {
        parent::__construct();

        /*
        * Allow access to private Services
        *  https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
        */
        self::bootKernel();

        $this->consumptionStatsHelper = self::$container->get(ConsumptionStatsHelper::class);

        $this->consumptionCollection = new ConsumptionCollection();
    }

    public function testGetTotalConsumption()
    {

        $this->assertEquals(
            0,
            $this->consumptionStatsHelper->getTotalConsumption($this->consumptionCollection)
        );

        $this->consumptionCollection->add(new Consumption('2019-01-01', 0, 500));
        $this->assertEquals(
            0.5, //kWh
            $this->consumptionStatsHelper->getTotalConsumption($this->consumptionCollection)
        );

        $this->consumptionCollection->add(new Consumption('2019-01-01', 1, 500));
        $this->assertEquals(
            1, //kWh
            $this->consumptionStatsHelper->getTotalConsumption($this->consumptionCollection)
        );

        $this->consumptionCollection->add(new Consumption('2019-01-02', 1, 1000));
        $this->assertEquals(
            2, //kWh
            $this->consumptionStatsHelper->getTotalConsumption($this->consumptionCollection)
        );

    }


    public function testGetAggregatedConsumptionByDayOfWeek()
    {
        $expectedResultEmpty = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
            6 => 0,
            7 => 0,
        ];

        $expectedResult = $expectedResultEmpty;

        /* Test result empty structure */
        $this->assertEquals(
            $expectedResult,
            $this->consumptionStatsHelper->getAggregatedConsumptionByDayOfWeek($this->consumptionCollection)
        );

        /* Same day, different hours (sum) */
        $this->consumptionCollection->add(new Consumption('2019-01-01', 0, 500)); //Tuesday, day #2
        $this->consumptionCollection->add(new Consumption('2019-01-01', 1, 500)); //Tuesday, day #2
        $expectedResult[2] = 1; //kWh

        $this->assertEquals(
            $expectedResult,
            $this->consumptionStatsHelper->getAggregatedConsumptionByDayOfWeek($this->consumptionCollection)
        );


        /* Different days */
        $this->consumptionCollection->add(new Consumption('2019-01-02', 0, 500)); //Wednesday, day #3
        $this->consumptionCollection->add(new Consumption('2019-01-03', 1, 500)); //Thursday, day #4
        $expectedResult[3] = 0.5; //kWh
        $expectedResult[4] = 0.5; //kWh

        $this->assertEquals(
            $expectedResult,
            $this->consumptionStatsHelper->getAggregatedConsumptionByDayOfWeek($this->consumptionCollection)
        );

    }

    public function testGetAggregatedConsumptionByHour()
    {
        $expectedResultEmpty = [
            0 => 0,     1 => 0,      2 => 0,
            3 => 0,     4 => 0,      5 => 0,
            6 => 0,     7 => 0,      8 => 0,
            9 => 0,     10 => 0,    11 => 0,
            12 => 0,    13 => 0,    14 => 0,
            15 => 0,    16 => 0,    17 => 0,
            18 => 0,    19 => 0,    20 => 0,
            21 => 0,    22 => 0,    23 => 0,
        ];

        $expectedResult = $expectedResultEmpty;

        /* Test result empty structure */
        $this->assertEquals(
            $expectedResult,
            $this->consumptionStatsHelper->getAggregatedConsumptionByHour($this->consumptionCollection)
        );


        /* Different days, same hour (sum) */
        $this->consumptionCollection->add(new Consumption('2019-01-01', 0, 500));
        $this->consumptionCollection->add(new Consumption('2019-01-02', 0, 500));
        $expectedResult[0] = 1; //kWh

        $this->assertEquals(
            $expectedResult,
            $this->consumptionStatsHelper->getAggregatedConsumptionByHour($this->consumptionCollection)
        );

        /* Same day, different hours */
        $this->consumptionCollection->add(new Consumption('2019-01-01', 23, 500));
        $this->consumptionCollection->add(new Consumption('2019-01-01', 12, 500));
        $expectedResult[23] = 0.5; //kWh
        $expectedResult[12] = 0.5; //kWh

        $this->assertEquals(
            $expectedResult,
            $this->consumptionStatsHelper->getAggregatedConsumptionByHour($this->consumptionCollection)
        );
    }

    public function testFindTwoConsecutiveHoursWithGreaterConsumption()
    {
        $this->consumptionCollection->add(new Consumption('2019-01-01', 0, 500));
        $this->consumptionCollection->add(new Consumption('2019-01-01', 1, 500));
        $this->consumptionCollection->add(new Consumption('2019-01-01', 2, 500));
        $this->consumptionCollection->add(new Consumption('2019-01-01', 3, 500));

        $this->assertEquals(
            [0,1],
            $this->consumptionStatsHelper->findTwoConsecutiveHoursWithGreaterConsumption()
        );
    }

    public function testConsumptionDuring50HoursWithGreaterConsumption()
    {
        $this->assertEquals(
            0,
            $this->consumptionStatsHelper->consumptionDuring50HoursWithGreaterConsumption($this->consumptionCollection)
        );

        for ($i = 0; $i <= 23; ++$i) {
            $this->consumptionCollection->add(new Consumption('2019-01-01', $i, $i*100));
            $this->consumptionCollection->add(new Consumption('2019-01-02', $i, $i*100));
            $this->consumptionCollection->add(new Consumption('2019-01-03', $i, $i*100));
        }

        $this->assertEquals(
            array_sum([
                2.3*3, 2.2*3, 2.1*3, 2.0*3,
                1.9*3, 1.8*3, 1.7*3, 1.6*3,
                1.5*3, 1.4*3, 1.3*3, 1.2*3,
                1.1*3, 1.0*3, 0.9*3, 0.8*3,
                0.7*2
            ]),
            $this->consumptionStatsHelper->consumptionDuring50HoursWithGreaterConsumption($this->consumptionCollection)
        );
    }

    public function testConsumptionInTempoSolar()
    {
        /* Winter consumption */
        //Generate day consumption
        for ($i = 0; $i <= 23; ++$i) {
            $this->consumptionCollection->add(new Consumption('2019-01-01', $i, $i*100));
        }

        $this->assertEquals(
            array_sum([1.7, 1.8, 1.9, 2.0, 2.1, 2.2, 2.3, 0.0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9]),
            $this->consumptionStatsHelper->consumptionInTempoSolar($this->consumptionCollection)
        );

        /* Summer consumption */
        $this->consumptionCollection->clear();
        //Generate day consumption
        for ($i = 0; $i <= 23; ++$i) {
            $this->consumptionCollection->add(new Consumption('2019-07-01', $i, $i*100));
        }

        $this->assertEquals(
            array_sum([1.8, 1.9, 2.0, 2.1, 2.2, 2.3, 0.0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0]),
            $this->consumptionStatsHelper->consumptionInTempoSolar($this->consumptionCollection)
        );

    }
}