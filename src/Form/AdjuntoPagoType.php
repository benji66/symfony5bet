<?php

namespace App\Form;

use App\Entity\AdjuntoPago;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdjuntoPagoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ruta')
            ->add('monto')
            ->add('validado_by')
            ->add('numero_referencia')
            ->add('observacion')
                   
            //->add('metodo_pago')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AdjuntoPago::class,
        ]);
    }
}
