<?php

namespace App\Form;

use App\Entity\MyDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MyDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url')
            ->add('category')
            ->add('title')
            ->add('client')
            ->add('rh')
            ->add('consultant')
            ->add('manager')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyDocument::class,
        ]);
    }
}
