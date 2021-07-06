<?php

namespace App\DataFixtures;

use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // La boucle FOR tourne 10 fois car nous voulons créer 10 articles
        for($i = 1; $i <= 11; $i++)
        {
            // Pour pouvoir insérer des données dans la table SQL article, nous devons instancier son entité correspondante (Article), Symfony se sert l'objet entité $article pour injecter les valeurs dans les requetes SQL
            $article = new Article;

            // On fait appel aux setteurs de l'objet entité afin de renseigner les titres, les contenu, les images et les dates des faux articles stockés en BDD
            $article->setTitre("Titre de l'article $i")
                    ->setContenu("<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sagittis neque diam, eu lacinia metus ultricies et. Pellentesque lobortis velit id commodo vestibulum. Nulla ut rutrum dui. Nulla quis malesuada neque. Praesent et nulla a eros finibus hendrerit et non erat. Proin varius mauris et lorem pharetra elementum. Pellentesque faucibus enim nec tempor lobortis. Duis laoreet elementum mauris, nec porta ex scelerisque ullamcorper. Proin sodales a urna nec condimentum. Nulla purus augue, gravida et lacus convallis, scelerisque tincidunt justo. Donec dictum mauris urna, id tempus dui pharetra at. Nunc eget vehicula quam.</p>")
                    ->setImage("https://picsum.photos/600/600")
                    ->setDate(new \DateTime());

            // Un manager (ObjectManager) en Symfony est un classe permettant, entre autre, de manipuler les lignes de la BDD (INSERT, UPDATE, DELETE)

            // persist() : méthode issue de la classe ObjectManager permettant de préaprer et de garder en méméoire les requetes d'insertion
            // $data = $bdd->prepare("INSERT INTO article VALUES ('getTitre()', 'getContenu()' etc...)")
            $manager->persist($article);
        }

        // flush() : méthode issue de la classe ObjectManager permettant véritablement d'executer les requetes d'insertions en BDD
        $manager->flush(); // execute()
    }
}
