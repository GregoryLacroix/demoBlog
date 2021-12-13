<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
    #[Route('/admin/article/{id}/remove', name: 'app_admin_article_remove')]
    public function adminArticles(EntityManagerInterface $manager, ArticleRepository $repoArticle, Article $artRemove = null): Response
    {
        // dd($artRemove);
        // 
        $colonnes = $manager->getClassMetadata(Article::class)->getFieldNames();
        // dd($colonnes);

        // SELECT * FORM article + FETCH_ALL
        $articles = $repoArticle->findAll();
        // dd($articles);

        // Traitement suppression article en BDD
        // Si la condition IF retourne TRUE, cela veut dire que $artRemove contient l'article a supprimer en BDD, on entre dans le IF 
        if($artRemove)
        {
            // Avant de supprimer l'article dans la bdd, on stock son ID afin de l'intégrer dans la message de validation de suppression (addFlash)
            $id = $artRemove->getId();

            // remove() : méthode issue de l'interface EntityManagerInterface permettant de formuler une requete SQL de suppression (DELETE)
            $manager->remove($artRemove);
            $manager->flush();

            $this->addFlash('success', "l'article n° $id a été supprimé avec succès.");

            // Après la suppression, on redirige l'internaute vers l'affichages des articles
            return $this->redirectToRoute('app_admin_articles');
        }

        return $this->render('back_office/admin_articles.html.twig', [
            'colonnes' => $colonnes,
            'articles' => $articles
        ]);
    }


    #[Route('/admin/article/add', name: 'app_admin_article_add')]
    #[Route('/admin/article/{id}/update', name: 'app_admin_article_update')]
    public function adminArticleForm(Request $request, EntityManagerInterface $manager, Article $article = null): Response
    {
        // Si $article contient un article de la BDD, on stock une variable la photo de l'article afin de la renvoyer en BDD si nous ne modifions pas la photo de l'article
        if($article)
        {
            $photoActuelle = $article->getPhoto();
        }

        if(!$article)
        {
            $article = new Article;
        }
        
        $formAdminArticle = $this->createForm(ArticleType::class, $article);

        $formAdminArticle->handleRequest($request);

        if($formAdminArticle->isSubmitted() && $formAdminArticle->isValid())
        {
            // Si l'article possède un ID, alors c'est une modification, on change le texte dans le message de validation
            if($article->getId())
                $txt = 'modifié';
            else 
                $txt = 'enregistré';

            if(!$article->getId())
                $article->setDate(new \DateTime());

            // dd($article);

            // DEBUT TRAITEMENT PHOTO 
            $photo = $formAdminArticle->get('photo')->getData();
            // dd($photo);

            // Si une photo a été uploadée dans le formulaire
            if($photo)
            {
                $nomOriginePhoto = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // dd($nomOriginePhoto);

                $nouveauNomFichier = $nomOriginePhoto . '-' . uniqid() . '.' . $photo->guessExtension();
                // dd($nouveauNomFichier);

                try // on tente de copier la photo dans le bon dossier
                {
                    $photo->move(
                        $this->getParameter('photo_directory'),
                        $nouveauNomFichier
                    );
                }
                catch(FileException $e)
                {

                }

                // on enregistre la photo en BDD
                $article->setPhoto($nouveauNomFichier);
            }
            else 
            {
                // Si l'article possède une photo mais qu'on ne souhaite pas la modifiée, alors on entre dans la condition IF et on renvoi la même photo dans la BDD
                if(isset($photoActuelle))
                    $article->setPhoto($photoActuelle);
                else 
                    // Sinon on crée un nouvel article mais on ne souhaite uplodée d'image, alors on envoi NULL pour la champ photo dans la BDD
                    $article->setPhoto(null);
            }

            // FIN TRAITEMENT PHOTO

            $manager->persist($article);
            $manager->flush();

            $this->addFlash('success', "L'article a été $txt avec succès.");

            return $this->redirectToRoute('app_admin_articles');
        }

        return $this->render('back_office/admin_article_form.html.twig', [
            'formAdminArticle' => $formAdminArticle->createView(),
            'photoActuelle' => $article->getPhoto() // renvoi la photo de l'article pour l'afficher en cas de modification
        ]);
    }

    /*
        Exo : affichage et suppression catégorie 
        1. Création d'une nouvelle route '/admin/categories' (name: app_admin_categories)
        2. Création d'une nouvelle méthode adminCategories()
        3. Création d'un nouveau template 'admin_categories.html.twig'
        4. Selectionner les noms des champs/colonnes de la table Category, les transmettre au template et les afficher 
        5. Selectionner dans le controller l'ensemble de la table 'category' (findAll) et transmettre au template (render) et les afficher sur le template (Twig), afficher également le nombre d'article liés à chaque catégorie
        6. Prévoir un lien 'modifier' et 'supprimer' pour chaque categorie
        7. Réaliser le traitement permettant de supprimer une catégorie de la BDD
    */

    #[Route('/admin/categories', name: 'app_admin_categories')]
    public function adminCategories(EntityManagerInterface $manager, CategoryRepository $repoCategory): Response
    {
        $colonnes = $manager->getClassMetadata(Category::class)->getFieldNames();
        // dd($colonnes);

        $allCategory = $repoCategory->findAll();
        // dd($allCategory);

        return $this->render('back_office/admin_categories.html.twig', [
            'colonnes' => $colonnes,
            'allCategory' => $allCategory
        ]);
    }
}
