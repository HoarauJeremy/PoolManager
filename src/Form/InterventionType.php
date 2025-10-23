<?php

namespace App\Form;

use App\Entity\Client;
use App\Entity\Intervention;
use App\Entity\Materiel;
use App\Entity\TypeIntervention;
use App\Entity\User;
use App\Enum\Status;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire Symfony lié à l’entité Intervention.
 *
 * Ce formulaire permet la création et la modification d’une intervention.
 * Il contient tous les champs nécessaires : libellé, dates, adresse, client, techniciens, etc.
 */
class InterventionType extends AbstractType
{
    /**
     * Construit le formulaire.
     *
     * Chaque champ correspond à une propriété de l'entité Intervention.
     * Les attributs "attr" définissent le style et le comportement HTML des inputs.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ============================================
            // 🏷️ Libellé de l’intervention
            // ============================================
            ->add('libelle', null, [
                'label' => false,
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Libellé de l’intervention',
                ],
            ])

            // ============================================
            // 📅 Date de début
            // ============================================
            ->add('date_debut', null, [
                'widget' => 'single_text', // Permet d’utiliser un input type="date"
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                ],
            ])

            // ============================================
            // 📅 Date de fin
            // ============================================
            ->add('date_fin', null, [
                'widget' => 'single_text',
                'placeholder' => 'date de fin',
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'date de fin',
                ],
            ])

            // ============================================
            // 🏠 Adresse
            // ============================================
            ->add('adresse', null, [
                'label' => false,
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Adresse complète',
                ],
            ])

            // ============================================
            // 🌆 Ville
            // ============================================
            ->add('ville', null, [
                'label' => false,
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Ville',
                ],
            ])

            // ============================================
            // 📮 Code postal
            // ============================================
            ->add('code_postal', null, [
                'label' => false,
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Code postal',
                ],
            ])

            // ============================================
            // 📝 Informations supplémentaires
            // ============================================
            ->add('infos', null, [
                'label' => false,
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                    'placeholder' => 'Informations supplémentaires',
                ],
            ])

            // ============================================
            // ⚙️ Statut de l’intervention (Enum)
            // ============================================
            ->add('status', EnumType::class, [
                'class' => Status::class, // Enum définissant les statuts possibles (PLANIFIER, ENCOURS, etc.)
                'label' => false,
                'placeholder' => 'Sélectionner un status',
                'choice_label' => fn(Status $status) => $status->label(), // Affiche le label lisible de chaque statut
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                ],
            ])

            // ============================================
            // 🧩 Type d’intervention
            // ============================================
            ->add('type', EntityType::class, [
                'label' => false,
                'class' => TypeIntervention::class,
                'choice_label' => 'nom', // Affiche le champ "nom" de l’entité TypeIntervention
                'placeholder' => 'Type d\'intervention',
                'attr' => [
                    'class' => 'bg-white w-full p-2 my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                ],
            ])

            // ============================================
            // 🔧 Matériels utilisés (relation ManyToMany)
            // ============================================
            ->add('materiel', EntityType::class, [
                'class' => Materiel::class,
                // Affiche le libellé et la description du matériel
                'choice_label' => function (Materiel $materiel) {
                    return $materiel->getLibelle() . ' ' . $materiel->getDescription();
                },
                'multiple' => true, // Plusieurs matériels peuvent être associés
                'expanded' => true,
                'placeholder' => 'Matériels',
                'attr' => [
                    'class' => 'flex flex-col text-center bg-white w-full p-2 my-4 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                ],
                'choice_attr' => function () {
                    // Chaque ligne (input + label) sur la même ligne
                    return ['class' => 'flex items-center gap-2'];
                },
            ])

            // ============================================
            // 👤 Client concerné (relation ManyToOne)
            // ============================================
            ->add('client', EntityType::class, [
                'label' => false,
                'class' => Client::class,
                // Affiche le nom + prénom du client
                'choice_label' => function (Client $client) {
                    return $client->getNom() . ' ' . $client->getPrenom();
                },
                'placeholder' => 'Client',
                'attr' => [
                    'class' => 'bg-white w-full my-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                ],
            ])

            // ============================================
            // 👷 Techniciens affectés (relation ManyToMany)
            // ============================================
            ->add('techniciens', EntityType::class, [
                'class' => User::class,
                // Affiche le nom + prénom du technicien
                'choice_label' => function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },

                'multiple' => true, // Plusieurs techniciens peuvent être associés à la même intervention
                'expanded' => true,
                'placeholder' => 'Techniciens',
                'attr' => [
                    'class' => 'flex flex-col text-center bg-white w-full p-2 my-4 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                ],
            ]);
    }

    /**
     * Configure les options par défaut du formulaire.
     *
     * Ici, on indique que ce formulaire manipule l’entité Intervention.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}
