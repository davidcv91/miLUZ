<?php
namespace App\Entity;


class ConsumptionParser
{
    private $fileHandler;

    /**
     * @param \SplFileObject $fileHandler
     */
    public function __construct($fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    /**
     * @return ConsumptionCollection $consumptionCollection
     */
    public function getData() : ConsumptionCollection
    {
        $consumptionCollection = new ConsumptionCollection();

        $this->fileHandler->seek(6);

        while (!$this->fileHandler->eof()) {
            $line = explode(' , ', $this->fileHandler->getCurrentLine());

            if (count($line) == 5) {
                $consumption = new Consumption(
                    $line[0], //date
                    substr($line[1], 0, 2), //hour
                    $line[2] //consumption (Wh)
                );

                $key = $consumption->getId();
                if ($consumptionCollection->containsKey($key)) {
                    if ($consumption->getConsumption() > $consumptionCollection->get($key)->getConsumption()) {
                        $consumptionCollection->set($key, $consumption);
                    }
                }
                else {
                    $consumptionCollection->set($key, $consumption);
                }
            }
        }

        return $consumptionCollection;
    }
}