<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
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
    public function adminArticles(EntityManagerInterface $manager, ArticleRepository $repoArticle, Article $artRemove = null, Request $request): Response
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
            'photoActuelle' => $article->getPhoto(), // renvoi la photo de l'article pour l'afficher en cas de modification
            'editMode' => $article->getId()
        ]);
    }

    #[Route('/admin/categories', name: 'app_admin_categories')]
    #[Route('/admin/categorie/{id}/remove', name: 'app_admin_categorie_remove')]
    public function adminCategories(EntityManagerInterface $manager, CategoryRepository $repoCategory, Category $category = null): Response
    {
        $colonnes = $manager->getClassMetadata(Category::class)->getFieldNames();
        // dd($colonnes);

        $allCategory = $repoCategory->findAll(); // SELECT * FROM category + FETCH_ALL
        // dd($allCategory);

        // Traitement suppression catégorie
        // dd($category);
        if($category)
        {
            // On récupère le titre de la catègorie avant la suppression pour l'intégrer dans le message utilisateur
            $titreCat = $category->getTitre();

            // getArticles() retourne tout les articles liés à la catégorie, si le resultat est vide, cela veut dire qu'aucun article n'est lié à la catégorie, on entre dans le IF et on supprime la catégorie
            if($category->getArticles()->isEmpty())
            {
                $this->addFlash('success', "La catégorie '$titreCat' a été supprimé avec succès.");

                $manager->remove($category);
                $manager->flush();
            }
            else // Sinon, des articles sont encore liés à la catégorie, alors on affiche un message d'erreur à l'utilisateur
            {
                $this->addFlash('danger', "Impossible de supprimer la catégorie '$titreCat' car des articles y sont toujours associés.");
            }

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('back_office/admin_categories.html.twig', [
            'colonnes' => $colonnes,
            'allCategory' => $allCategory
        ]);
    }

    #[Route('/admin/categorie/add', name: 'app_admin_categorie_add')]
    #[Route('/admin/categorie/{id}/update', name: 'app_admin_categorie_update')]
    public function adminCategorieForm(Request $request, EntityManagerInterface $manager, Category $category = null): Response
    {
        if(!$category)
            $category = new Category;

        $formCategory = $this->createForm(CategoryType::class, $category);

        $formCategory->handleRequest($request);

        if($formCategory->isSubmitted() && $formCategory->isValid())
        {
            // dd($category);
            if($category->getId())
                $txt = 'modifiée';
            else 
                $txt = 'enregistrée';

            $manager->persist($category);
            $manager->flush();

            // On stock le titre de la catégorie dans une variable afin de l'intégrer dans le message de validation
            $titreCat = $category->getTitre();

            $this->addFlash('success', "La catégorie '$titreCat' a été $txt avec succès.");

            return $this->redirectToRoute('app_admin_categories');
        }
        
        return $this->render('back_office/admin_categorie_form.html.twig', [
            'formCategory' => $formCategory->createView(),
            'editMode' => $category->getId(),
        ]);
    }

    #[Route('/admin/commentaires', name: 'app_admin_commentaires')]
    #[Route('/admin/commentaire/{id}/remove', name: 'app_admin_commentaire_remove')]
    public function adminCommentaires(EntityManagerInterface $manager, CommentRepository $repoComment): Response
    {
        // on selectionne le nom des champs/colonnes
        $colonnes = $manager->getClassMetadata(Comment::class)->getFieldNames();
        // dd($colonnes);

        $commentaires = $repoComment->findAll();
        // dd($commentaires);

        return $this->render('back_office/admin_commentaires.html.twig', [
            'colonnes' => $colonnes,
            'commentaires' => $commentaires
        ]); 
    }
}
