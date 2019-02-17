<?php

namespace App\DataFixtures;

use App\Entity\Consumption;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        try {
            $startDate = (new \DateTime())->modify('-30 days');
            $todayDate = new \DateTime();

            while($startDate->format('Y-m-d') != $todayDate->format('Y-m-d') ) {
                $startDate->modify('+1 day');
                for($hour = 0; $hour <= 23; ++$hour) {
                    $consumption = new Consumption(
                        $startDate->format('Y-m-d'),
                        $hour,
                        rand(10, 350)
                    );
                    $manager->persist($consumption);
                }
            }
        }
        catch (\Exception $e) {}

        $manager->flush();
    }
}
