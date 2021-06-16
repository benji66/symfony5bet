<?php

namespace App\Form;

use App\FormPerfilType;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
      

       /*if(isset($options['data']) && $options['data']->getPassword() != '1234567') 
           echo $options['data']->getPassword(); 
        else 
            echo '#0000FF';

     
exit;*/
        $builder
            ->add('email', EmailType::class)
            ->add('roles', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'choices'  => [
                    //'ROLE_OTRO' => 'ROLE_OTRO',
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                    'ROLE_GERENCIA' => 'ROLE_GERENCIA',
                    'ROLE_COORDINADOR' => 'ROLE_COORDINADOR',


                   
                ],
            ])
            ->add('password', RepeatedType::class, array(
                'required' => false, 
                'empty_data' => '',

                'data' =>  '#0000FF',
      
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Clave:'),
                'second_options' => array('label' => 'Confirmar clave:'),
            ));

            $builder->add('perfil', PerfilType::class);
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
