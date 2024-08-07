<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $categories = [
            ['Electronics', 'Devices and gadgets'],
            ['Books', 'Printed and digital books'],
            ['Clothing', 'Apparel and accessories'],
            ['Home & Kitchen', 'Furniture and kitchen appliances'],
            ['Toys', 'Children’s toys and games']
        ];

        foreach ($categories as $i => [$name, $description]) {
            $category = new Category();
            $category->setName($name)
                ->setDescription($description);

            $manager->persist($category);

            $this->addReference('category_' . ($i + 1), $category);
        }

        $manager->flush();
    }
}
