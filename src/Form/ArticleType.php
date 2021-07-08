<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // la fonction add permet de créer les champs du fomulaire
        $builder
            ->add('titre', TextType::class, [
                'attr' => [
                    'placeholder' => "Saisir le titre de l'article"
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label' => "Contenu de l'article",
                'attr' => [
                    'placeholder' => "Saisir l'article",
                    'rows' => 15
                ]
            ])
            ->add('image', TextType::class, [
                'attr' => [
                    'placeholder' => "Saisir l'URL de l'image"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
