<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Constraint\FileExists;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class BlogController extends AbstractController
{

    #[Route('/', name: 'home')]
    public function home(): Response 
    {
        // méthode rendu, en fonction de la route dans l'URL, la méthode render() envoi un template, un rendu sur le navigateur
        return $this->render('blog/home.html.twig', [
            'title' => 'Bienvenue sur le blog Symfony',
            'age' => 25
        ]);
    }

    # cette méthode permet de selectionner toute les catégories de la BDD mais ne possède pas de route, les catégories seront intégrées dans base.html.twig
    public function allCategory(CategoryRepository $repoCategory)
    {
        $categorys = $repoCategory->findAll();

        return $this->render('blog/categorys_list.html.twig', [
            'categorys' => $categorys
        ]);
    }

    # Méthode permettant d'afficher l'ensemble des articles en BDD
    #[Route('/blog', name: 'blog')]
    #[Route('/blog/categorie/{id}', name: 'blog_categorie')]
    public function blog(ArticleRepository $repoArticle, Category $category = null): Response
    {
        // dd($category->getArticles());
        /*
            Injections de dépendances : c'est un des fondement de Symfony, ici notre méthode DEPEND de la classe ArticleRepository pour fonctionner correctement
            Ici Symfony comprend que la méthode blog() attend en arguement un objet issu de la classe ArticleRepository, automatiquement Symfony envoi une instance de cette classe en argument de cette classe
            $repoArticle est un objet issu de la classe ArticleRepository, nous n'avons plus qu'à piocher dans l'objet pour atteindre des méthodes de la classe

            Symfony est une application qui est capable de répondre à un navigateur lorsque celui-ci appel une addresse (ex: localhost:8000/blog), le controller doit être capable d'envoyer un rendu, un template sur le navigateur

            Ici, lorsque l'on tranmset la route '/blog' dans l'URL, cela execute la méthode index() dans le controller qui renvoie le template '/blog/inddex.html.twig' sur la navigateur
        */

        // Pour selectionner des données en BDD, nous devons passer par une classe Repository, ces classes permettent uniquement d'executer des requetes de selection SELECT en BDD. ELles contiennent des méthodes mis à disposition par Symfony (findAll(), find(), findBy() etc...)

        // Ici nous devons importer au sein de notre controller la classe Article Repository pour pouvoir selectionner dans la table Article
        // $repoArticle est un objet issu de la classe ArticleRepository
        // getRepository() est une méthode issue de l'objet Doctrine permettant ici d'importer la classe ArticleRepository

        // $repoArticle = $doctrine->getRepository(Article::class);

        // dump() / dd() : outil de debug de Symfony
        // dd($repoArticle);

        // Si la condition retourne TRUE, cela veut dire que l'utilisateur a cliqué sur le lien d'une catégorie dans la nav et par conséquent, $category contient une catégorie stocké en BDD, alors on entre dans la condition IF
        if($category)
        {
            // Grace aux relations bi-directionnelle, lorsque nous selectionnons une catagorie en BDD, nous avons accès automatiquement à tous les articles liés à cette catégorie
            // getArticles() retourne un array multi contenant tout les articles liés à la catégorie transmise dans l'URL
            $articles = $category->getArticles();
        }
        else // Sinon aucune catégorie n'est transmise dans l'URL, alors on selectionne tout les articles dans la BDD
        {
            // findAll() : méthode issue de la classe ArticleRepository permettant de selectionner l'ensemble de la table SQL et de récuperer un tableau multi contenant l'ensemble des articles stocké en BDD
            $articles = $repoArticle->findAll(); // SELECT * FROM article + FETCH_ALL
            // dd($articles); // dump()
        }

        return $this->render('blog/blog.html.twig', [
            'articles' => $articles // on transmet au template les articles selectionnés en BDD afin que twig traite l'affichage
        ]);
    }

    # Méthode permettant d'insérer / modifier un article en BDD
    #[Route('/blog/new', name: 'blog_create')]
    #[Route('/blog/{id}/edit', name: 'blog_edit')]
    public function blogCreate(Article $article = null, Request $request, EntityManagerInterface $manager, SluggerInterface $slugger): Response 
    {
        // La classe Request de SYmfony contient toute les données véhiculées par les superglobales ($_GET, $_POST, $_SERVER, $_COOKIE etc...)
        // $request->request : la propriété 'request' de l'objet $request contient toute les données de $_POST

        // Si les données dans le tableau ARRAY $_POST sont supérieur à 0, alors on entre dans la condition IF
        // if(count($_POST) > 0)
        // if($request->request->count() > 0)
        // {
            // $request->$_POST
            // dd($request->request);

            // Pour insérer dans la table SQL 'article', nous avons besoin d'un objet de son entité correspondante 
            // $article = new Article;

            //      ->setTitre($_POST['titre'])
            // $article->setTitre($request->request->get('titre'))
            //         ->setContenu($request->request->get('contenu'))
            //         ->setPhoto($request->request->get('photo'))
            //         ->setDate(new \DateTime());

            // dd($article);

            // persist() : méthode issue de l'interface EntityManagerInterface permettant de préparer la requete d'insertion et de garder en mémoire l'objet / la requete
            //$manager->persist($article);

            // flush() : méthode issue de l'interface EntityManagerInterface permettant véritablement d'executer la requete INSERT en BDD (ORM doctrine)
            //$manager->flush();
        //}

        // Si la condition IF retourne TRUE, cela veut dire que $article contient un article stocké en BDD, on stock la photo actuelle de l'article dans la variable $photoActuelle
        if($article)
        {
            $photoActuelle = $article->getPhoto();
        }

        // Si la variable $article est null, cela veut dire que nous sommes sur la route '/blog/new', on entre dans le IF et on crée une nouvelle instance de l'entité Article
        // Si la variable $article n'est pas null, cela veut dire que nous sommes sur la route '/blog/{id}/edit', nous n'entrons pas dans le IF car $article contient un article de la BDD
        if(!$article)
        {
            $article = new Article;
        }

        // $article->setTitre("Titre à la con")
        //         ->setContenu("Contenu à la con");

        $formArticle = $this->createForm(ArticleType::class, $article);

        // $article->setTitre($_POST['titre'])
        // $article->setContenu($_POST['contenu'])
        // handleRequest() permet d'envoyer chaque données de $_POST et de les transmettre aux bon setter de l'objet entité $article
        $formArticle->handleRequest($request);

        // Si le formulaire a bien été validé (isSubmitted) et que l'objet entité est correctement remplit (isValid) alors on entre dans le condition IF
        if($formArticle->isSubmitted() && $formArticle->isValid())
        {
            // Le seul setter que l'on appel de l'entité, c'est celui de la date puisqu'il n'y a pas de champ 'date' dans le formulaire

            // Si l'article ne possède pas d'id, c'est une insertion, alors on entre dans la condition IF et on génère une date d'article
            if(!$article->getId())
                $article->setDate(new \DateTime());

            // DEBUT TRAITEMENT PHOTO
            // On récupère toute les informations de l'image uplodé dans le formulaire
            $photo = $formArticle->get('photo')->getData();

            if($photo) // Si une photo est uploadé dans le formulaire, on entre dans le IF et on traite l'image
            {
                // On récupère le nom d'origine de la photo
                $nomOriginePhoto = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // dd($nomOriginePhoto);

                // cela est nécessaire pour inclure en toute sécurité le nom du fichier dans l'URL
                $secureNomPhoto = $slugger->slug($nomOriginePhoto);

                //                   Santana            -   84484848484  .    jpg
                $nouveauNomFichier = $secureNomPhoto . '-' . uniqid() . '.' . $photo->guessExtension();
                // dd($nouveauNomFichier);

                try // on tente de copier l'image dans le dossier
                {
                    // On copie l'image vers le bon chemin, vers le bon dossier 'public/uploads/photos' (services.yaml)
                    $photo->move(
                        $this->getParameter('photo_directory'),
                        $nouveauNomFichier
                    );
                }
                catch(FileException $e)
                {

                }

                // on insère le nom de l'image dans la BDD
                $article->setPhoto($nouveauNomFichier);
            }
            else // Sinon aucune image n'a été uplodé, on renvoi dans la bdd la photo actuelle de l'article
            {
                // Si la photo actuelle est définit en BDD, alors en cas de modifications, si on ne change pas de photo, on renvoi la photo actuelle en BDD
                if(isset($photoActuelle))
                    $article->setPhoto($photoActuelle);
                else 
                    // Sinon aucune photo n'a été uploadé, on envoi la valeur NULL en BDD pour la photo
                    $article->setPhoto(null);
            }

            // FIN TRAITEMENT PHOTO

            // Message de validation en session 
            if(!$article->getId())
                $txt = "enregistré";
            else 
                $txt = "modifié";

            // Méthode permettant d'enregistrer des messages utilisateurs accessibles en session 
            $this->addFlash('success', "L'article a été $txt avec succès !");

            $manager->persist($article);
            $manager->flush();

            // Une fois l'insertion/modification executée en BDD, on redirige l'internaute vers le détail de l'article, on transmet l'id à fournir dans l'url en 2ème paramètre de la méthode redirectToRoute()
            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('blog/blog_create.html.twig', [
            'formArticle' => $formArticle->createView(), // on transmet le formulaire au template afin de pouvoir l'afficher avec Twig
            // createView() retourne un petit objet qui représente l'affichage du formulaire, on le récupère dans le template blog_create.html.twig
            'editMode' => $article->getId(),
            'photoActuelle' => $article->getPhoto()
        ]);
    }

    # Méthode permettant d'afficher le détail d'un article
    # On définit un route 'paramétrée' {id}, ici la route permet de recevoir l'id d'un article stocké en BDD
    #        /blog/5
    #[Route('/blog/{id}', name: 'blog_show')]
    public function blogShow(Article $article, Request $request, EntityManagerInterface $manager): Response
    {
        // Cette méthode mise à disposition retourne un objet App\Entity\Article contenant toute les données de l'utilisateur authentifié sur le site
        $user = $this->getUser();
        // dd($user);

        $comment = new Comment;

        $formComment = $this->createForm(CommentType::class, $comment, [
            'commentFormFront' => true // on indique dans quelle condition IF on entre dans le fichier 'App\From\CommentType' et quel formulaire nous affichons
        ]);

        // $comment->setAuteur($_POST['auteur'])
        // $comment->setCommentaire($_POST['commentaire'])
        $formComment->handleRequest($request);

        if($formComment->isSubmitted() && $formComment->isValid())
        {
            // dd($this->getUser());
            // getUser() : méthode se Symfony qui retourne un objet (App\Entity\User) contenant les informations de l'utilisateur authentifié sur le blog
            $user = $this->getUser();

            $comment->setDate(new \DateTime())
                    ->setAuteur($user->getPrenom() . ' ' . $user->getNom())
                    ->setArticle($article); // on relie le commentaire l'article

            // dd($comment);

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success', "Félicitations ! Votre commentaire a bien été posté !");

            return $this->redirectToRoute('blog_show', [
                'id' => $article->getId()
            ]);
        }

        /*
            Ici, nous envoyons un ID dans l'url et nous imposons en argument un objet issu de l'entité Article donc la table SQL 
            Donc Symfony est capable de selecionner en BDD l'article en focntion de l'id passé dans l'url et de l'envoyer automatiquement en argument de la méthode blogShow() dans la variable de reception $article 
        */

        // On importe la classe ArticleRepository dans la méthode BlogShow pour selectionner (SELECT) dans la table SQL 'article'
        // $repoArticle = $doctrine->getRepository(Article::class);

        // find() : méthode issue de la classe ArticleRepository permettant de selectionner un élément par son ID qu'on lui fournit en argument 
        // $article = $repoArticle->find($id);
        // dd($article);

        // L'id transmit dans la route '/blog/5' est tramsit automatiquement en arguùent de la méthode blogShow($id) dans la variable de réception $id
        // dd($id); // 5
        return $this->render('blog/blog_show.html.twig', [
            'article' => $article, // On transmet au template l'article selectionné en BDD afin que Twig puisse traiter et afficher les données sur la page
            'formComment' => $formComment->createView()
        ]);
    }
}
