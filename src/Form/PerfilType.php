<?php

namespace App\Form;


use App\Entity\Perfil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class PerfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre')
            ->add('apellido')
            ->add('telefono')
            ->add('nickname')

            
           
            //->add('user_id')
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