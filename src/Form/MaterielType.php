<?php

namespace App\Form;

use App\Entity\Materiel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

/**
 * Formulaire pour l'entité Materiel
 * 
 * Ce formulaire gère la saisie et la validation des données pour créer
 * ou modifier un matériel. Il inclut des contraintes de validation pour
 * s'assurer que les données saisies sont correctes.
 */
class MaterielType extends AbstractType
{
    /**
     * Construit le formulaire avec tous les champs nécessaires
     * 
     * Cette méthode définit les champs du formulaire avec leurs types
     * et leurs contraintes de validation appropriées.
     * 
     * @param FormBuilderInterface $builder Constructeur de formulaire
     * @param array $options Options de configuration du formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ libellé avec validation obligatoire
            ->add('libelle', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le libellé ne peut pas être vide.'])
                ]
            ])
            // Champ quantité avec validation obligatoire et positive
            ->add('quantite', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La quantité ne peut pas être vide.']),
                    new PositiveOrZero(['message' => 'La quantité doit être positive ou nulle.'])
                ]
            ])
            // Champ description avec validation obligatoire
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La description ne peut pas être vide.'])
                ]
            ])
        ;
    }

    /**
     * Configure les options par défaut du formulaire
     * 
     * Cette méthode définit la classe de données associée au formulaire
     * et toute autre option de configuration nécessaire.
     * 
     * @param OptionsResolver $resolver Résolveur d'options pour le formulaire
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Materiel::class,
        ]);
    }
}
