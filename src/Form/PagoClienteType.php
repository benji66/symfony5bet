<?php

namespace App\Form;

use App\Entity\PagoCliente;
use App\Entity\MetodoPago;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Perfil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PagoClienteType extends AbstractType
{
   private $token;
    public function __construct(TokenStorageInterface $token){
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        //echo $user->getPerfil()->getGerencia()->getId();
        //exit;
        $builder
            ->add('perfil', EntityType::class, [
                    // looks for choices from this entity
                    'class' => Perfil::class,

                    'query_builder' => function(EntityRepository $er){
                        $user = $this->token->getToken()->getUser();
                        $gerencia_id = $user->getPerfil()->getGerencia()->getId();

                        return $er->createQueryBuilder('a')
                                ->innerJoin('a.usuario','u')
                                ->andWhere('a.gerencia = :gerencia')
                                //->andWhere('a.sueldo > 0')
                                ->orderBy('u.email')
                                ->setParameter('gerencia', $gerencia_id);
                    },
                    // uses the User.username property as the visible option string
                    'choice_label' => 'nickname',

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
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
          
            ->add('observacion')            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PagoCliente::class,
        ]);
    }
}
