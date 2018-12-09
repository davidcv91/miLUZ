<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="consumptions")
 * @ORM\Entity(repositoryClass="App\Repository\ConsumptionRepository")
 */
class Consumption
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="Invalid value: {{ value }}")
     * @Assert\DateTime(message="Invalid value: {{ value }}")
     */
    private $datetime;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank(message="Invalid value: {{ value }}")
     * @Assert\GreaterThanOrEqual(value="0", message="Invalid value: {{ value }}")
     */
    private $consumption; //unit: Wh

    public function __construct($date, $hour, $consumption)
    {
        $this->datetime = \DateTime::createFromFormat('Y-m-d H', $date.' '.$hour);
        $this->consumption = $consumption;

        if ($this->datetime) {
            $this->id = $this->datetime->format('U');
        }
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    public function getConsumption()
    {
        return $this->consumption;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setDatetime(\DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function setConsumption(float $consumption): self
    {
        $this->consumption = $consumption;

        return $this;
    }
}