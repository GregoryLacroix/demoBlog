<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
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
    public function blog(ArticleRepository $repoArticles): Response
    {
        // Pour selectionner des données dans une table SQL en BDD? nous devons importer la classe Repository qui correspond à la table SQL, c'est à dire à l'entité correspondante (Article)
        // Une classe Repository permet uniquement de formuler et d'executer des requetes SQL de selection (SELECT)
        // Cette classe contient des méthodes mis à disposition par Symfony pour formuler et executer des requetes SQL en BDD 
        // traitements requete selection BDD des articles 
        // $repoArticles est un objet issu de la classe ArticleRepository
        // getRepository() : méthode permettant d'importer la classe Repository d'une entité 
        // $repoArticles = $this->getDoctrine()->getRepository(Article::class);

        // Contrôle : 
        // dump() : outil de debug propre à Symfony
        dump($repoArticles); // dd()

        // findAll() : SELECT * FROM article + FETCHALL
        // $articles : tableau ARRAY multidimensionnel contenant l'ensemble des articles stockés dans la BDD
        $articles = $repoArticles->findAll();
        dump($articles);

        return $this->render('blog/blog.html.twig', [
            'articlesBDD' => $articles // on transmet au template les articles que nous avons selectionnés en BDD afin de les traités et de les afficher avec le langage TWIG
            // extract()
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
     * Méthode permettant de créer un nouvel article et de modifier un article existant
     * 
     * @Route("/blog/new", name="blog_create")
     */
    public function create(): Response 
    {
        return $this->render('blog/create.html.twig');
    }

    /**
     * Méthode permettant d'afficher le détail d'un article
     * 
     * /blog/6
     * 
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article): Response 
    {
        // L'id transmit dans l'URL est envoyé directement en argument de la fonction show(), ce qui nous permet d'avoir accès à l'id de l'article a selectionner en BDD au sein de la méthode show()
        // dump($id); // 6

        // Importation de la classe ArticleRepository
        // $repoArticle = $this->getDoctrine()->getRepository(Article::class);
        // dump($repoArticle);

        // find() : méthode mise à dispostion par Symfony issue de la classe ArticleRepository permettant de selectionner un élément de la BDD par son ID 
        // $article : tableau ARRAY contenant toutes les données de l'article selectionné en BDD en fonction de l'ID transmit dans l'URL 

        // SELECT * FROM article WHERE id = 6 + FETCH 
        // $article = $repoArticle->find($id); // 6
        dump($article);

        return $this->render('blog/show.html.twig', [
            'articleBDD' => $article // on transmet au template les données de l'article selectionné en BDD afin de les traiter avec le langage Twig dans le template
        ]);
    }
}



