<?php

namespace App\Form;

use App\FormPerfilGerenciaType;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {    
        $builder
            ->add('email', EmailType::class)
            ->add('nombre')
            ->add('apellido')
            ->add('telefono')            
            
            ->add('password', RepeatedType::class, array(
                'required' => false, 
                'empty_data' => '',
                'data' =>  '#0000FF',      
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Clave:'),
                'second_options' => array('label' => 'Confirmar clave:'),
            ));
       
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => function (FormInterface $form) {
                    $data = $form->getData();                                      

                    if ($data->getId()) {
                        return ['edit'];
                    }

                    return ['User'];
                },
        ]);
    }
}
