<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    
    #[Route("/login", name:"app_login")]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si getUser() renvoi des données, cela veut que l'internaute est authentifié donc inscrit, il n'a rien à faire sur la route '/login', on le redirige vers la route du blog '/blog'
        if($this->getUser()) 
        {
            return $this->redirectToRoute('blog');
        }

        // renvoie un message d'erreur si jamais nous avons saisi les mauvais identifiants pour la connexion
        $error = $authenticationUtils->getLastAuthenticationError();

        // cette méthode renvoie dans notre cas, le dernier 'email' saisi par l'internaute dans le formulaire d'authentification
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route("/logout", name:"app_logout")]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
