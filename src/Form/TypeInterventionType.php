<?php

namespace App\Form;

use App\Entity\TypeIntervention;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Classe de définition du formulaire pour l'entité TypeIntervention.
 *
 * Un FormType Symfony permet de décrire la structure du formulaire,
 * les champs qu’il contient, et la manière dont ils sont liés à une entité.
 */
class TypeInterventionType extends AbstractType
{
    /**
     * Cette méthode construit le formulaire.
     * 
     * Le FormBuilder permet d’ajouter les champs à afficher dans le formulaire
     * et de configurer leurs types, labels, options, validations, etc.
     *
     * @param FormBuilderInterface $builder  L’objet qui construit le formulaire
     * @param array $options  Tableau d’options passées au formulaire
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On ajoute un champ correspondant à la propriété "nom" de l’entité TypeIntervention.
        // Symfony déduit automatiquement le type du champ (ici TextType) grâce au mapping Doctrine.
        $builder
            ->add('nom', null, [
                'label' => 'Nom du type d’intervention', // Texte du label affiché
                'attr' => [
                    'class' => 'w-full p-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none',
                    'placeholder' => 'Ex : Maintenance, Nettoyage, Réparation…',
                ],
            ])
        ;
    }

    /**
     * Cette méthode configure les options par défaut du formulaire.
     * 
     * Ici, on indique à Symfony que le formulaire est lié à l’entité TypeIntervention.
     * Ainsi, lors de la soumission, les données du formulaire seront directement
     * mappées à un objet TypeIntervention.
     *
     * @param OptionsResolver $resolver  Permet de définir les options par défaut
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Indique que ce formulaire manipule des instances de TypeIntervention
            'data_class' => TypeIntervention::class,
        ]);
    }
}
