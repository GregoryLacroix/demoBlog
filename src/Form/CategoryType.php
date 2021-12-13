<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => "Saisir le titre de la catégorie"
                ],
                "constraints" => [
                    new NotBlank([
                        'message' => "Merci de saisir le titre de la catégorie."
                    ]),
                    new Length([
                        'max' => 15,
                        'maxMessage' => "Titre de catégorie trop long (15 caractères maximum)"
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => "Saisir la description de la catégorie."
                ],
                "constraints" => [
                    new NotBlank([
                        'message' => "Merci de saisir la description de la catégorie."
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
