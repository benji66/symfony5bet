<?php

namespace App\Form;

use App\Entity\Traspaso;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Perfil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TraspasoType extends AbstractType
{
    private $token;
    public function __construct(TokenStorageInterface $token){
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
             
            
        $builder     
            ->add('descuento', EntityType::class, [
                    // looks for choices from this entity
                    'class' => Perfil::class,

                    'query_builder' => function(EntityRepository $er){
                        $user = $this->token->getToken()->getUser();
                        $gerencia_id = $user->getPerfil()->getGerencia()->getId();

                        return $er->createQueryBuilder('a')
                                ->innerJoin('a.usuario','u')
                                ->andWhere('a.gerencia = :gerencia')
                                //->andWhere('a.saldo > 0')
                                ->orderBy('u.email')
                                ->setParameter('gerencia', $gerencia_id);
                    },
                    // uses the User.username property as the visible option string
                    'choice_label' => 'nickname',

             ])

            ->add('abono', EntityType::class, [
                    // looks for choices from this entity
                    'class' => Perfil::class,

                    'query_builder' => function(EntityRepository $er){
                        $user = $this->token->getToken()->getUser();
                        $gerencia_id = $user->getPerfil()->getGerencia()->getId();

                        return $er->createQueryBuilder('a')
                                ->innerJoin('a.usuario','u')
                                ->andWhere('a.gerencia = :gerencia')
                                 ->andWhere('a.saldo > 0')
                                ->orderBy('u.email')
                                ->setParameter('gerencia', $gerencia_id);
                    },
                    // uses the User.username property as the visible option string
                    'choice_label' => 'nickname' ,

             ])               
            ->add('monto')
             ->add('observacion',null, [
                'label'=>'Razon'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Traspaso::class,
        ]);
    }
}
