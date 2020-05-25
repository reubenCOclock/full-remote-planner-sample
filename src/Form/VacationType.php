<?php

namespace App\Form;

use App\Entity\Vacation;
use App\Form\SubVacationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType; 
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;




class VacationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
        
        $builder
            ->add('type_of_vacation',ChoiceType::class ,[
                'label'=> 'Type de congé','label_attr'=>['class'=>'font-weight-bold'],
                'choices'=>[
                    'CP'=>'CP',
                    'RTT'=>'RTT',
                    'Congé sans solde'=>'Congé sans solde'

                ]
            ])
            ->add('start_date',DateType::class, ['label'=> 'Date de début','widget'=>'single_text','label_attr'=>['class'=>'font-weight-bold']])
            ->add('end_date',DateType::class, ['label'=> 'Date de fin','widget'=>'single_text','label_attr'=>['class'=>'font-weight-bold']])
            ->add('subVacations',CollectionType::class,[
                'entry_type'=>SubVacationType::class,
                'allow_add'=>true,
                'by_reference'=>false,
                'entry_options'=>['label'=>false],
                'label'=>false

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vacation::class,
        ]);
    }
}
