<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => "Titre de l'article",
                'required' => false,
                'attr' => [
                    'placeholder' => "Saisir le titre de l'article",
                ],
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'max' => 50,
                        'minMessage' => "Titre trop court (min 10 caractères).",
                        'maxMessage' => "Titre trop long (max 50 caractères)."
                    ]),
                    new NotBlank([
                        'message' => "Merci de saisir un titre d'article."
                    ])
                ]
            ])
            // On définit le chmp permettant d'associer une catégorie à l'article dans le formulaire
            // Ce champ provient d'une autre entité, en gros c'est la clé étrangère
            ->add('category', EntityType::class, [
                'label' => "Choisir une catégorie",
                'class' => Category::class, // On précise de quelle entité vient ce champ
                'choice_label' => 'titre' // on définit la valeur qui apparaitra dans la liste déroulante
            ])
            ->add('contenu', TextareaType::class, [
                'label' => "Contenu de l'article",
                'required' => false,
                'attr' => [
                    'placeholder' => "Saisir le contenu de l'article.",
                    'rows' => 10
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => "Merci de saisir un contenu d'article."
                    ])
                ]
            ])
            ->add('photo', FileType::class, [
                'label' => "Uploader une photo",
                'mapped' => true, // signifie que le champ est associé à une propriété et qu'il sera inséré en BDD
                'required' => false,
                'data_class' => null,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg'
                        ],
                        'mimeTypesMessage' => 'Formats autorisés : jpg/jpeg/png.'
                    ])
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
