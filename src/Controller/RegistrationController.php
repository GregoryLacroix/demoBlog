<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $encoder): Response
    {
        // UserPasswordHasherInterface : interface permettant d'encoder le mot de passe

        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user);
        
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) 
        {
            // On fait appel à l'objet $encoder afin de hacher le mot de passe 
            // hashPassword() : méthode issue de UserPasswordHasherInterface permettant de créer une clé de hachage pour le mot de passe
            $hash = $encoder->hashPassword($user, $user->getPassword());

            dump($hash);

            // On affecte à l'entité le mot de passe haché qui sera inséré en BDD
            $user->setPassword($hash);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
