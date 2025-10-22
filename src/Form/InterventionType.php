<?php

namespace App\Form;

use App\Entity\client;
use App\Entity\Intervention;
use App\Entity\Materiel;
use App\Entity\TypeIntervention;
use App\Entity\user;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', null, [
                'label' => false,
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Libellé de l’intervention',
                ],
            ])
            ->add('date_debut', null, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',

                ],
            ])
            ->add('date_fin', null, [
                'widget' => 'single_text',
                'placeholder' => 'date de fin',
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'date de fin',
                ],
            ])
            ->add('adresse', null, [
                'label' => false,
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Adresse complète',
                ],
            ])
            ->add('ville', null, [
                'label' => false,
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Ville',
                ],
            ])
            ->add('code_postal', null, [
                'label' => false,
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Code postal',
                ],
            ])
            ->add('infos', null, [
                'label' => false,
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Informations supplémentaires',
                ],
            ])
            ->add('status', null, [
                'label' => false,
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Status',
                ],
            ])
            ->add('type', EntityType::class, [
                'label' => false,
                'class' => TypeIntervention::class,
                'choice_label' => 'nom',
                'placeholder' => 'Type d\'intervention',

                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                ],
            ])
            ->add('materiel', EntityType::class, [
                //'label' => false,
                'class' => Materiel::class,
                'choice_label' => function (Materiel $materiel) {
                    return $materiel->getLibelle() . ' ' . $materiel->getDescription();
                }, // Renvoi le Libellé + la description
                'multiple' => true,
                'placeholder' => 'Matériels',
                'attr' => [
                    'class' => 'text-center bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                ],
            ])
            ->add('client', EntityType::class, [
                'label' => false,
                'class' => Client::class,
                'choice_label' => function (Client $client) {
                    return $client->getNom() . ' ' . $client->getPrenom();
                }, // Renvoi le nom + le prénom
                'placeholder' => 'Client',
                'attr' => [

                    'class' => 'bg-white w-full my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                ],
            ])
            ->add('technicens', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                }, // Renvoi le nom + le prénom
                'multiple' => true,
                'placeholder' => 'Techniciens',
                'attr' => [
                    'class' => 'text-center bg-white w-full p-2 my-4 border border-gray-300  focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                ],
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}
