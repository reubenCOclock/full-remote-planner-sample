<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EmptyRecapConsultantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oui',SubmitType::class,[
                'label'=>'OUI',
                'label_attr'=>['class'=>'btn btn-primary bg-vbl rounded-pill bg-warning']
                
            ])
            ->add('non',SubmitType::class,[
                'label'=>'NON',
                'label_attr'=>['class'=>'btn btn-primary bg-vbl rounded-pill bg-warning']
                
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
