<?php

namespace App\Form;

// Import des classes nécessaires
use App\Entity\Client;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    // Méthode pour construire le formulaire
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajout des champs du formulaire
        $builder
            ->add('nom')            // Champ pour le nom de famille
            ->add('prenom')         // Champ pour le prénom
            ->add('email')          // Champ pour l'adresse email
            ->add('adresse')        // Champ pour l'adresse postale
            ->add('ville')          // Champ pour la ville
            ->add('code_postal')    // Champ pour le code postal
            ->add('tel_fixe')       // Champ pour le numéro de téléphone fixe
            ->add('tel_gsm')        // Champ pour le numéro de portable
        ;
    }

    // Configuration des options du formulaire
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Définit la classe associée au formulaire (Client)
            'data_class' => Client::class,
        ]);
    }
}