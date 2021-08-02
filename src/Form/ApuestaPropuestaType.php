<?php

namespace App\Form;

use App\Entity\ApuestaPropuesta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\ApuestaTipo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ApuestaPropuestaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ApuestaPropuesta::class,
        ]);
    }
}
