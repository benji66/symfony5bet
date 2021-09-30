<?php

namespace App\Form;

use App\Entity\RetiroSaldo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use App\Entity\MetodoPago;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class RetiroSaldoEditType extends AbstractType
{
    private $token;
    public function __construct(TokenStorageInterface $token){
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      

        $builder
       
                 ->add('metodo_pago', EntityType::class, [
                    // looks for choices from this entity
                    'class' => MetodoPago::class,
                    // uses the User.username property as the visible option string
                    'choice_label' => 'nombre',
                    
                    'query_builder' => function(EntityRepository $er){
                        $user = $this->token->getToken()->getUser();
                        $gerencia_id = $user->getPerfil()->getGerencia()->getId();

                        return $er->createQueryBuilder('mp')                              
                                ->andWhere('mp.gerencia = :gerencia')
                                ->andWhere('mp.activo = true')
                                ->orderBy('mp.nombre')
                                ->setParameter('gerencia', $gerencia_id);
                       
                    },
                ])
            ->add('validado', ChoiceType::class, [
                'multiple' => false,
                'expanded' => true,
                'choices'  => [
                    'APROBADO' => '1',
                    'RECHAZADO' => '0',                   
                ],
            ])   
            ->add('numero_referencia')  
            ->add('observacion')         
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RetiroSaldo::class,
        ]);
    }
}
