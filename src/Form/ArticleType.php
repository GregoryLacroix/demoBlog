<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => "Titre de l'article",
                'attr' => [
                    'placeholder' => "Saisir le titre de l'article",
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label' => "Contenu de l'article",
                'attr' => [
                    'placeholder' => "Saisir le contenu de l'article",
                    'rows' => 10
                ]
            ])
            ->add('photo', TextType::class, [
                'label' => "URL de la photo",
                'attr' => [
                    'placeholder' => "Saisir l'URL de la photo",
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
