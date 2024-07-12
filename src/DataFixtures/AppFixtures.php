<?php

namespace App\DataFixtures;

use App\Entity\Widget;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $widget1 = new Widget();
        $widget1->setName("Test Widget 1");
        $manager->persist($widget1);

        $widget2 = new Widget();
        $widget2->setName("Test Widget 2");
        $manager->persist($widget2);

        $manager->flush();
    }
}
