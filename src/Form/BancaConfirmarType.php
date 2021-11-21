<?php

namespace App\Form;

use App\Entity\Banca;
use App\Entity\Perfil;
use App\Entity\ApuestaTipo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BancaConfirmarType extends AbstractType
{
    private $token;
    public function __construct(TokenStorageInterface $token){
        $this->token = $token;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
         
            ->add('tipo', EntityType::class, [
                    // looks for choices from this entity
                    'class' => ApuestaTipo::class,

                    // uses the User.username property as the visible option string
                    'choice_label' => 'nombre',

                ])        
            
        ;
    }

   
}
