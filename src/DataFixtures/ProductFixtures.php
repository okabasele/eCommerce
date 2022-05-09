<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Product;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        
        $faker = \Faker\Factory::create('fr_FR');
        for ($i = 1; $i < 3; $i++) {
            $category= new Category;
            $category->setTitle($faker->sentence(3));
            
            $manager->persist($category);

            for ($j=0; $j < mt_rand(8, 10); $j++) { 
                $product = new Product;
                $product->setName($faker->sentence(3))
                ->setDescription('<p>'. join('</p><p>', $faker->paragraphs(3)) . '</p>')
                ->setPrice($faker->randomFloat(2, 10, 100)) //2 décimales, min 10 , max 100
                ->setImage($faker->imageUrl)
                ->setCategoryId($category);
                $manager->persist($product);

                for ($k=0; $k < mt_rand(5,10) ; $k++) { 
                    $comment = new Comment;
                    $content = '<p>'. join('</p><p>', $faker->paragraphs(5)) . '</p>';
                    $comment->setContent($content)
                    ->setCreatedAt($faker->dateTimeBetween('-10 days'))
                    ->setProductId($product);

                    $manager->persist($comment);
                }
            }

        }

        $manager->flush();
    }
}
