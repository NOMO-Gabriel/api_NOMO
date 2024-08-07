<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Faker\Factory;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $roles = [
            ['ROLE_USER'],
            ['ROLE_USER', 'ROLE_EDIT'],
            ['ROLE_USER', 'ROLE_ADMIN']
        ];

        foreach ($roles as $i => $roleSet) {
            $user = new User();
            $user->setUsername('user_' .$i)
                ->setPassword($this->passwordEncoder->HashPassword($user, 'password'))
                ->setRoles($roleSet)
                ->setEmail('user_' .$i. '@test.com');
            $manager->persist($user);

            // Store the user reference if needed for other fixtures
            $this->addReference('user_' . $i, $user);
        }

        $manager->flush();
    }
}
