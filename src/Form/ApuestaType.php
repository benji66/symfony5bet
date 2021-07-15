<?php

namespace App\Form;

use App\Entity\Apuesta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\ApuestaTipo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ApuestaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('busca_perfil',null, [

                'attr' => ['placeholder'=>'buscar persona'],
                'mapped'=>false,

            ])
            ->add('monto')
             ->add('tipo', EntityType::class, [
                    // looks for choices from this entity
                    'class' => ApuestaTipo::class,

                    // uses the User.username property as the visible option string
                    'choice_label' => 'nombre',

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ])
            //->add('corre_lista')
            //->add('validado')
            //->add('validado_by')
            //->add('carrera')          
            //->add('perfils')
            //->add('ganador')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Apuesta::class,
        ]);
    }
}
