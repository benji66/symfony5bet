<?php

namespace App\Form;


use App\Entity\Perfil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Gerencia;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class PerfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
             ->add('gerencia', EntityType::class, [
                    // looks for choices from this entity
                    'class' => Gerencia::class,

                    // uses the User.username property as the visible option string
                    'choice_label' => 'nombre',

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ])

            

            ->add('nickname')

            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'choices'  => [
                    //'ROLE_OTRO' => 'ROLE_OTRO',
                    //'ROLE_ADMIN' => 'ROLE_ADMIN',
                    //'ROLE_GERENCIA' => 'ROLE_GERENCIA',
                    'ROLE_COORDINADOR' => 'ROLE_COORDINADOR',


                   
                ],
            ])
            ->add('activo')
            ->add('saldo_ilimitado')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Perfil::class,
             'constraints' => [
                new UniqueEntity(['fields' => ['nickname']]),
            ],
        ]);
    }
}
//regex mac ^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$