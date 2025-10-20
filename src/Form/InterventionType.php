<?php

namespace App\Form;

use App\Entity\client;
use App\Entity\Intervention;
use App\Entity\Material;
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
            ->add('libelle')
            ->add('date_debut', null, [
                'widget' => 'single_text',
            ])
            ->add('date_fin', null, [
                'widget' => 'single_text',
            ])
            ->add('adresse')
            ->add('ville')
            ->add('code_postal')
            ->add('infos')
            ->add('status')
            ->add('type', EntityType::class, [
                'class' => TypeIntervention::class,
                'choice_label' => 'id',
            ])
            ->add('materiel', EntityType::class, [
                'class' => Material::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('client', EntityType::class, [
                'class' => client::class,
                'choice_label' => 'id',
            ])
            ->add('technicens', EntityType::class, [
                'class' => user::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
        ]);
    }
}
