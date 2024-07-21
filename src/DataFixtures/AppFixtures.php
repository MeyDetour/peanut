<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Payment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setPassword('$2y$13$1zFD2rA5vmlRDdx8asgaH.K.3iyaIvlS8HmYMASbcpJB.mNzEnchS');
        $user->setEmail('meydetour@gmail.com');
        $user->setVerified(false);
        $manager->persist($user);

        $manager->flush();
        $cats = [
            ['type' => 'outcome',
                'name' => 'Default outcome'],
            ['type' => 'income',
                'name' => 'Default income'],
            ['type' => 'outcome',
                'name' => 'Loyer',
                'montant' => 500]
        ];
        foreach ($cats as $cat) {
            $c = new Category();
            $c->setName($cat['name']);
            $c->setType($cat['type']);
            $c->setOwner($user);
            if (isset($cat['montant'])) {
                $c->setMontant($cat['montant']);
            }
            $manager->persist($c);
        }

        $payments = [
            ['type' => 'cash',
                'name' => 'Default Cash'],
            ['type' => 'card',
                'name' => 'Default Credit Card']
        ];
        foreach ($payments as $met) {
            $p = new Payment();
            $p->setName($met['name']);
            $p->setType($met['type']);
            $p->setOwner($user);
            $manager->persist($p);
        }
        $manager->flush();
    }
}
