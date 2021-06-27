<?php

namespace App\Form;

use App\Entity\AdjuntoPago;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use App\Entity\MetodoPago;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AdjuntoPagoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
             
              ->add('busca_perfil',null, [

                'attr' => ['placeholder'=>'buscar persona'],
                'mapped'=>false,

            ])

            ->add('perfil_id', HiddenType::class, [
                'mapped'=>false,

            ])
             ->add('metodo_pago', EntityType::class, [
                    // looks for choices from this entity
                    'class' => MetodoPago::class,
                    // uses the User.username property as the visible option string
                    'choice_label' => 'nombre',
                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ])
            ->add('monto')
            
            ->add('numero_referencia')
           
            /* ->add('validado', ChoiceType::class, [
                'multiple' => false,
                'expanded' => true,
                'choices'  => [
                    'PENDIENTE' => 'null', 
                    'APROBADO' => 'true',
                    'RECHAZADO' => 'false',                   
                ],
            ])  */ 
             ->add('observacion')         
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdjuntoPago::class,
        ]);
    }
}
