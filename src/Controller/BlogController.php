<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/blog/new_old", name="blog_create_old")
     */
    public function createOld(Request $request, EntityManagerInterface $manager): Response
    {
        dump($request);

        // La classe Request permet de stocker et d'avoir accès aux données véhiculées par les superglobales ($_POST, $_GET, $_COOKIE, $_FILES etc...)

        // la propriété $request->request permet de stocker et d'accéder aux données saisie dans le formulaire, c'est à dire aux données de la superglobale $_POST 
        // Si les données sont supérieurs à 0, donc si nous avons bien saisie des données dans le formulaire, alors on entre dans la condition IF
        if($request->request->count() > 0) 
        {
            // Si nous vouclons insérer des données dans la table SQL Article, nous devons instancier et remplir un objert issu de son entité correspondante (classe Article)
            $article = new Article;

            // On renseigne tout les setteurs de l'objet avec les données saisie dans le formulaire 
            // request->request->get('titre') : permet d'atteindre la valeur du titre saisi dans le champ 'titre' du formulaire
            $article->setTitre($request->request->get('titre')) // pull marron
                    ->setContenu($request->request->get('contenu'))
                    ->setImage($request->request->get('image'))
                    ->setDate(new \DateTime());

            // Pour manipuler les lignes de la BDD (INSERT, UPDATE, DELETE), nous avons besoin d'un mamager (EntityManagerInterface) 
            // persist() : méthode issue de l'interface EntityManagerInterface permettant de préparer et garder en mémmoire la requete d'insertion
            // $data = $bdd->prepare("INSERT INTO article VALUES ('$article->getTitre()', '$article->getContenu()')")
            $manager->persist($article);

            // flush() : méthode issue de l'interface EntityManagerInterface permettant veritablement d'executer le requete d'insertion en BDD
            // $data->execute()
            $manager->flush();

            dump($article);

            // Après l'insertion de l'article en BDD, nous redirigeons l'internaute vers le'affichage du détail de l'article, donc une autre route via la méthode redirectToRoute()
            // Cette méthode attend 2 arguments 
            // 1. La route 
            // 2. le paramètre a transmettre dans la route, dans notre cas l'ID de l'article
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('blog/create.html.twig');
    }

    /**
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function create(Article $article = null, Request $request, EntityManagerInterface $manager): Response 
    {
        // Si la variable $article N'EST PAS (null), si elle ne contient aucun article de la BDD, cela veut dire nous avons envoyé la route '/blog/new', c'est une insertion, on entre dans le IF et on crée une nouvelle instance de l'entité Article, création d'un nouvel article
        // Si la variable $article contient un article de la BDD, cela veut dire que nous avons envoyé la route '/blog/id/edit', c'est une modifiction d'article, on entre pas dans le IF, $article ne sera pas null, il contient un article de la BDD, l'article à modifier
        if(!$article)
        {
            $article = new Article;
        }

        // En renseignant les setteurs de l'entité, on s'aperçoit que les valeurs sont envoyés directement dans les attributs 'value' du formulaire, cela est dû au fait que l'entité $article est relié au formulaire
        // $article->setTitre("Titre bidon")
        //         ->setContenu("Contenu bidon");

        dump($request);

        // createForm() permet ici de créer un formulaire d'ajout d'article en fonction de la classe ArticleType
        // En 2ème argument de createForm(), nous transmettons l'objet entité $article afin de préciser que le formulaire a pour but de remplir l'objet $article, on relie l'entité au formulaire 
        $formArticle = $this->createForm(ArticleType::class, $article);

        // handleRequest() permet ici dans notre cas, de récupérer toute les données saisie dans le formulaire et de les transmettre aux bon setteurs de l'entité $article 
        // handleRequest() renseigne chaque setteur de l'entité $article avec les données saisi dans le formulaire
        $formArticle->handleRequest($request);

        dump($article);

        // Si le formulaire a bien été validé && que toute les données saisie sont bien transmise à la bonne entité, alors on entre dans la condition IF
        if($formArticle->isSubmitted() && $formArticle->isValid())
        {
            // on renseigne le setteur de la date, puisque nous n'avons pas de champ 'date' dans le formulaire
            // Si l'article ne possède pas d'ID, alors on entre dans la condition IF et on execute le setteur de la date, on entre dans le IF que dans le cas de la création d'un nouvel article 
            if(!$article->getId())
            {
                $article->setDate(new \DateTime());
            }

            // Pour manipuler les lignes de la BDD (INSERT, UPDATE, DELETE), nous avons besoin d'un mamager (EntityManagerInterface) 
            // persist() : méthode issue de l'interface EntityManagerInterface permettant de préparer et garder en mémmoire la requete d'insertion
            // $data = $bdd->prepare("REPLACE INTO article VALUES ('$article->getTitre()', '$article->getContenu()')")
            $manager->persist($article);

            // flush() : méthode issue de l'interface EntityManagerInterface permettant veritablement d'executer le requete d'insertion en BDD
            // $data->execute()
            $manager->flush();

            // On redirige l'internaute après l'insertion de l'article
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('blog/create2.html.twig', [
            'formArticle' => $formArticle->createView(), // on transmet le formulaire au template afin de pouvoir l'afficher avec Twig 
            // createView() va retourner un petit objet qui représente l'affichage du formulaire, on le récupère dans le template create2.html.twig
            'editMode' => $article->getId() // on transmet l'id de l'article au template 
        ]);
    }

    /**
     * Méthode permettant d'afficher le détail d'un article
     * 
     * /blog/6
     * 
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article, Request $request, EntityManagerInterface $manager): Response
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

        // TRAITEMENT COMMENTAIRE ARTICLE (formulaire + insertion)
        $comment = new Comment; 

        $formComment = $this->createForm(CommentType::class, $comment); 

        $formComment->handleRequest($request); // $comment->setAuteur('$_POST[auteur]') | $comment->setCommentaire('$_POST[commentaire]')

        if($formComment->isSubmitted() && $formComment->isValid())
        {
            $comment->setDate(new \DateTime());

            // On établit la realtion entre le commentaire et l'article (clé étrangère)
            // setArticle() : méthode issue de l'entité Comment qui permet de rensigner l'article associé au commentaire
            // Cette méthode attends en argument l'objet entité Article de la BDD et non la clé étrangère elle même
            $comment->setArticle($article);

            $manager->persist($comment);
            $manager->flush();

            // addFlash() : méthode permettant de déclarer un message de validation stocké en session
            // arguements :
            // 1. Identifiant du message (success)
            // 2. Le message utilisateur
            $this->addFlash('notice', "Le commentaire a été posté avec succès !");

            /*
                session
                array(
                    success => [
                        0 => "Le commentaire a été posté avec succès !"
                    ]
                )
            */

            dump($comment);

            // Après l'insertion, on redirige l'internaute vers l'affichage de l'article afin de rebooter le formulaire
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('blog/show.html.twig', [
            'articleBDD' => $article, // on transmet au template les données de l'article selectionné en BDD afin de les traiter avec le langage Twig dans le template
            'formComment' => $formComment->createView()
        ]);
    }
}