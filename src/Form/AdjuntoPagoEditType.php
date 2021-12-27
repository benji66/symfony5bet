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

class AdjuntoPagoEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('validado', ChoiceType::class, [
                'multiple' => false,
                'expanded' => true,
                'choices'  => [
                    'APROBADO' => '1',
                    'RECHAZADO' => '0',                   
                ],
            ])   
              ->add('observacion',null, [
                'label'=>'Razon'
            ])         
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdjuntoPago::class,
        ]);
    }
}
