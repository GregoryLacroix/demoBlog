<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('auteur', TextType::class, [
                'label' => "Nom",
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir votre nom.'
                    ])
                ] 
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => "Saisir votre commentaire",
                'required' => false,
                'attr' => [
                    'rows' => 5
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir votre commentaire.'
                    ])
                ] 
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
