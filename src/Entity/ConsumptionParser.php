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
                    $line[1]-1, //hour
                    $line[2] //consumption (Wh)
                );

                $consumptionCollection->add($consumption);
            }
        }

        return $consumptionCollection;
    }
}