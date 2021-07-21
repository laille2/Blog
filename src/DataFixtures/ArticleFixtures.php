<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        // Créer 3 catégories fakées
        for ($i = 1; $i <= 3; $i++) {
            $category = new Category();
            $category->setTitle($faker->sentence())
                ->setDescription($faker->paragraph());

            $manager->persist($category);


            // Créer entre 4 et 6 articles fakées
            for ($j = 1; $j <= mt_rand(4, 6); $j++) {
                $article = new Article();

                $content = '<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>';

                $article->setTitle($faker->sentence())
                    ->setContent($content)
                    ->setImage($faker->imageUrl())
                    ->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween()->format('Y-m-d H:i:s')))
                    ->setCategory($category);

                $manager->persist($article);

                // Commentaires de l'article
                for ($k = 0; $k <= mt_rand(4, 10); $k++) {
                    $comment = new Comment();

                    $content = '<p>' . join('</p><p>', $faker->paragraphs(2)) . '</p>';


                    $days = (new \DateTime())->diff($article->getCreatedAt())->days;

                    $comment->setAuthor($faker->name)
                        ->setContent($content)
                        ->setCreatedAt(new \DateTimeImmutable($faker->dateTimeBetween('-' . $days . ' days')->format('Y-m-d H:i:s')))
                        ->setArticle($article);

                    $manager->persist($comment);
                }
                
            }
        }
        $manager->flush();
    }
}
