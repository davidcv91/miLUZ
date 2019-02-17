<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="consumption")
 * @ORM\Entity(repositoryClass="App\Repository\ConsumptionRepository")
 */
class Consumption
{
    const TEMPO_SOLAR_START_HOUR_SUMMER = 18;
    const TEMPO_SOLAR_END_HOUR_SUMMER = 10;
    const TEMPO_SOLAR_START_HOUR_WINTER = 17;
    const TEMPO_SOLAR_END_HOUR_WINTER = 9;

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

    public function getConsumptionKWh()
    {
        return $this->consumption/1000;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate() :string
    {
        return $this->datetime->format('Y-m-d');
    }

    public function getHour() :int
    {
        return $this->datetime->format('H');
    }

    public function getDayOfWeek() :int
    {
        return $this->datetime->format('N');
    }

    public function isInTempoSolar() :bool
    {
        $dst = $this->datetime->format('I'); //Daylight Saving Time (1 means summer time)

        $tempoSolarStartHour = ($dst == 1) ? self::TEMPO_SOLAR_START_HOUR_SUMMER : self::TEMPO_SOLAR_START_HOUR_WINTER;
        $tempoSolarEndHour = ($dst == 1) ? self::TEMPO_SOLAR_END_HOUR_SUMMER : self::TEMPO_SOLAR_END_HOUR_WINTER;

        return ($this->getHour() >= $tempoSolarStartHour or $this->getHour() <= $tempoSolarEndHour);
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