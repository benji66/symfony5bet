<?php

namespace App\Form;

use App\Entity\RetiroSaldo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use App\Entity\PerfilBanco;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class RetiroSaldoType extends AbstractType
{
 
    private $token;
    public function __construct(TokenStorageInterface $token){
        $this->token = $token;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
             
  

             ->add('perfil_banco', EntityType::class, [
                    // looks for choices from this entity
                    'class' => PerfilBanco::class,
                    // uses the User.username property as the visible option string
                    'choice_label' => 'nombre',
                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,

                    'query_builder' => function(EntityRepository $er){
                        $user = $this->token->getToken()->getUser();
                        $perfil_id = $user->getPerfil()->getId();

                        return $er->createQueryBuilder('a')                              
                                ->andWhere('a.perfil = :perfil')
                                ->andWhere('a.activo = true')
                                ->orderBy('a.nombre')
                                ->setParameter('perfil', $perfil_id);
                       
                    },
                ])    
            ->add('monto')
  
                   
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RetiroSaldo::class,
        ]);
    }
}
