<?php

namespace App\Form;

use App\Entity\Carrera;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\CallbackTransformer;

use App\Entity\Local;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class CarreraType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
             ->add('hipodromo', EntityType::class, [
                    // looks for choices from this entity
                    'class' => Local::class,

                    // uses the User.username property as the visible option string
                    'choice_label' => 'nombre',

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ])
            ->add('fecha')
            /*->add('fecha', DateTimeType::class, [
                'time_widget' => false,
                'date_widget' => 'choice',
                'date_format' => 'dd/MM/YYYY',
                'html5'=>false
            ])*/
            ->add('numero_carrera')
            //->add('cantidad_caballos')
            //->add('status')
            
            //->add('orden_oficial')

        ;


        $builder->get('fecha')->addModelTransformer(
            new CallbackTransformer(
                function($value){
                    if(!$value){
                        return new \DateTime('now');
                     }
                     return $value;   
                },

                function($value){
                    return $value;
                }                

            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Carrera::class,
        ]);
    }
}
