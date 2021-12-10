<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Si getUser() renvoi des données, cela veut que l'internaute est authentifié donc inscrit, il n'a rien à faire sur la route '/register', on le redirige vers la route du blog '/blog'
        if($this->getUser())
        {
            return $this->redirectToRoute('blog');
        }

        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user, [
            'userRegistration' => true // on précise dans quelle consition on entre dans la classe RegistrationFormType pour afficher un formulaire en particulier, la classe contient plusieurs formulaire
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            // on fait appel à l'objet $userPasswordHasher de l'interface UserPasswordHasherInterface afin d'encoder le mot de passe en BDD
            // En argument on lui fournit l'objet entité dans lequel nous allons encoder un élément ($user) et on lui fournit le mot de passe saisi dans le formulaire a encoder 
            $hash = $userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );

            // dd($hash);

            // On transmet au setter du password la clé de hachage à enregistrée en BDD
            $user->setPassword($hash);

            $this->addFlash('success', "Félicitations ! Vous êtes maintenant inscrit sur le site !");

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /*
        Exo : créer une page profil affichant les données de l'utilsateur authentifié
        1. Créer une nouvelle route '/profil'
        2. Créer une nouvelle méthode userProfil()
        3. Cette méthode renvoi un template 'registration/profil.html.twig'
        4. Afficher dans ce template les informations de l'utilisateur connecté
    */

    #[Route('/profil', name: 'app_profil')]
    public function userProfil(): Response
    {
        // Si getUser() est null, et ne renvoi aucune données, cela veut que l'internaute n'est pas authentifié, il n'a rien à faire sur la route '/profil', on le redirige vers la route de connexion '/login'
        if(!$this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        // retourne un objet App\Entity\User contenant les informations de l'utilisateur authentifié sur le site, ces données sont stockées dans le fichier de session de l'utilisateur
        $user = $this->getUser();
        // dd($user);

        return $this->render('registration/profil.html.twig', [
            'user' => $user
        ]);
    }

    # Méthode permettant de modifier les informations de l'utilisateur en BDD (sauf le mdp)
    #[Route('/profil/{id}/edit', name: 'app_profil_edit')]
    public function userProfilEdit(User $user, Request $request, EntityManagerInterface $manager): Response
    {
        // dd($user);

        $formUpdate = $this->createForm(RegistrationFormType::class, $user, [
            'userUpdate' => true
        ]);

        // $user->setPrenom($_POST['prenom'])
        $formUpdate->handleRequest($request);

        if($formUpdate->isSubmitted() && $formUpdate->isValid())
        {
            // dd($user);

            $manager->persist($user);
            $manager->flush();

            // Une fois que l'utilisateur a modifié ses informations de profil, on le redirige vers la route de deconnexion, on le deconnecte pour qu'il puisse après mettre à jour la session en s'authentifiant de nouveau
            return $this->redirectToRoute('app_logout');
        }

        return $this->render('registration/profil_edit.html.twig', [
            'formUpdate' => $formUpdate->createView()
        ]);
    }
}
