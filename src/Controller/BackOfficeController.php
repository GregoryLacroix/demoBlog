<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class BackOfficeController extends AbstractController
{
    # Méthode qui affiche la page Home du backoffice 
    #[Route('/admin', name: 'app_admin')]
    public function adminHome(): Response
    {
        return $this->render('back_office/index.html.twig');
    }

    # Méthode qui affiche la page Home du backoffice 
    #[Route('/admin/articles', name: 'app_admin_articles')]
    public function adminArticles(EntityManagerInterface $manager): Response
    {
        // 
        $colonnes = $manager->getClassMetadata(Article::class)->getFieldNames();
        // dd($colonnes);

        /*
            Exo : afficher sous forme de tableau HTML l'ensemble des articles stockés en BDD
            1. Selectionner en BDD l'ensemble de la table 'article' et transmettre le résultat à la méthode render()
            2. Dans le template 'admin_articles.html.twig', mettre en forme l'affichage des articles dans un tableau HTML 
            3. Afficher le nom de la catégorie de chaque article 
            4. Afficher le nombre de commentaire de chaque article
            5. Prévoir un lien modification/suppression pour chaque article
        */

        return $this->render('back_office/admin_articles.html.twig', [
            'colonnes' => $colonnes 
        ]);
    }
}
