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

class PerfilGerenciaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder           

            ->add('nickname')
            ->add('porcentaje_ganar')
            ->add('porcentaje_perder')

            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'choices'  => [
                    //'ROLE_OTRO' => 'ROLE_OTRO',
                    //'ROLE_ADMIN' => 'ROLE_ADMIN',
                    'GERENCIA' => 'ROLE_GERENCIA',
                    'CORREDOR' => 'ROLE_COORDINADOR',
                    'ADMINISTRATIVO' => 'ROLE_ADMINISTRATIVO', 
                    'EMPLEADO' => 'ROLE_EMPLEADO', 
                    //'PROVEEDOR' => 'ROLE_PROVEEDOR',                   
                ],
            ])            
            ->add('saldo_ilimitado')
            ->add('activo')
            ->add('sueldo')
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