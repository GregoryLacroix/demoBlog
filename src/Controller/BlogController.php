<?php

namespace App\Controller;

use App\Entity\Article;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlogController extends AbstractController
{
    /**
     * Méthode permettant d'afficher l'ensemble des articles du blog
     * 
     * @Route("/blog", name="blog")
     */
    public function blog(): Response
    {
        // traitements requete selection BDD des articles 
        // $repoArticles est un objet issu de la classe ArticleRepository
        $repoArticles = $this->getDoctrine()->getRepository(Article::class);
        dump($repoArticles);

        $articles = $repoArticles->findAll();
        dump($articles);

        return $this->render('blog/blog.html.twig', [
            'controller_name' => 'BlogController',
        ]);
    }

    /**
     * Méthode permettant d'afficher le rendu de la page d'accueil du blog Symfony 
     *
     * @Route("/", name="home")
     */
    public function home(): Response 
    {
        return $this->render('blog/home.html.twig', [
            'title' => 'Blog dédié à la musique, viendez voir, ça déchire !!!',
            'age' => 25
        ]);
    }

    /**
     * Méthode permettant d'afficher le détail d'un article
     * 
     * @Route("/blog/12", name="blog_show")
     */
    public function show(): Response 
    {
        return $this->render('blog/show.html.twig');
    }

}



