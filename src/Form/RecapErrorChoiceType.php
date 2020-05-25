<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RecapErrorChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('yes',SubmitType::class,['label'=>'oui','attr'=>['class'=>'btn btn-dark bg-vbl rounded-pill col']])
            ->add('no',SubmitType::class,['label'=>'non','attr'=>['class'=>'btn btn-dark bg-vbl rounded-pill col']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
