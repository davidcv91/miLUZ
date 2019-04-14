<?php


namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

class ConsumptionCollection extends ArrayCollection
{
    /**
     * @param string $key
     * @return Consumption
     */
    public function get($key)
    {
        return parent::get($key);
    }

    public function indexById()
    {
        foreach ($this->getIterator() as $consumption) {
            $this->removeElement($consumption);
            $this->set($consumption->getId(), $consumption);
        }

        return $this;
    }

    public function sortByDate()
    {
        $criteria = Criteria::create()
            ->orderBy(array("datetime" => Criteria::ASC));

        return $this->matching($criteria);
    }
}