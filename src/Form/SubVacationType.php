<?php

namespace App\Form;

use App\Entity\SubVacation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType; 
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SubVacationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('typeOfVacation',ChoiceType::class,['label'=>'type de congé','choices'=>['CP'=>'CP','RTT'=>'RTT','Congé sans solde'=>'Congé sans solde']])
            ->add('startDate',DateType::class,['label'=>'date de debut','widget'=>'single_text'])
            ->add('endDate',DateType::class,['label'=>'date de fin','widget'=>'single_text'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SubVacation::class,
        ]);
    }
}
