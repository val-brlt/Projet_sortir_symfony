<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo')
            ->add('prenom')
            ->add('nom')
            ->add('telephone')
            ->add('email')
            ->add('password', PasswordType::class )
         //   ->add('confirmePassword')
//             ->add('idSite', EntityType::class,
//                [
//                   'class' => Site::class,
//                    'choice_label' => 'name',
//                    'multiple' => true,
//                    'expanded' => false
//               ])
          //  ->add('maPhoto')
            ->add('Enregistrer',SubmitType::class)
            ->add('Annuler',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}