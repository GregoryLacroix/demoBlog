<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // On importe la librairie Faker pour les fixtures, cela nous permet de créer des faux articles, catégorie, commentaire plus évolués avec par exemples des faux, faux prénoms, dates aléatoires etc...
        $faker = \Faker\Factory::create('fr_FR');

        // Création de 3 catégories
        for($cat = 1; $cat <= 3; $cat++)
        {
            $category = new Category;

            $category->setTitre($faker->word)
                     ->setDescription($faker->paragraph());

            $manager->persist($category);

            // Création de 4 à 10 article par catégorie
            // mt_rand() : fonction prédéfinie PHP qui retourne un chiffre aléatoire en fonction des arguents transmit, ici un chiffre entre 4 et 10
            for($art = 1; $art <= mt_rand(4,10); $art++)
            {
                $article = new Article;

                // join() : fonction prédéfinie de PHP (alias : de implode) mais pour les chaine de caractères
                $contenu = '<p>' .  join('</p><p>', $faker->paragraphs(5)) . '</p>';

                $article->setTitre($faker->sentence())
                        ->setContenu($contenu)
                        ->setPhoto(null)
                        ->setDate($faker->dateTimeBetween('-6 months'))
                        ->setCategory($category); // on relie les articles aux categories décalrées ci-dessus, le setteur attend en argument l'objet entité entité $category pur créer la clé étrangère et non un int

                $manager->persist($article);

                // Création de 4 à 10 commentaires par article
                for($cmt = 1; $cmt <= mt_rand(4,10); $cmt++)
                {
                    $comment = new Comment;

                    // Traitement des dates
                    $now = new DateTime(); // retourne la date du jour 

                    $interval = $now->diff($article->getDate()); // retourne un timestamp (un temps en secondes) entre la date de création des articles et aujourd'hui

                    $days = $interval->days; // retourne le nombre de jour entre la date de création des articles et aujourd'hui // 100 jours 

                    // Traitement des paragraphes commentaire
                    $contenu = '<p>' . join('</p><p>', $faker->paragraphs(2)) . '</p>';

                    $comment->setAuteur($faker->name)
                            ->setCommentaire($contenu)
                            ->setDate($faker->dateTimeBetween("-$days days")) // -100 days
                            ->setArticle($article);

                    $manager->persist($comment);
                }
            }
        }

        $manager->flush();
    }
}
