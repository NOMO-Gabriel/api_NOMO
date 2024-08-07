<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 1; $i <= 5; $i++) {
            $category = $this->getReference('category_' . $i);

            for ($j = 0; $j < 5; $j++) {
                $product = new Product();
                $product->setName($faker->word)
                    ->setDescription($faker->sentence)
                    ->setPrice($faker->randomFloat(2, 10, 1000))
                    ->setQuantity($faker->numberBetween(1, 100))
                    ->setCreatedAt(new \DateTimeImmutable($faker->dateTimeThisYear()->format('Y-m-d H:i:s')));

                // Create a main image for the product
                $image = new Image();
                $image->setUrl($faker->imageUrl())
                    ->setDescription($faker->sentence);
                $manager->persist($image);

                $product->setMainImage($image);
                $product->addImage($image);

                // Assign category to product
                $product->setCategory($category);

                $manager->persist($product);
            }
        }

        $manager->flush();
    }
}
