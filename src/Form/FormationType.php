<?php

namespace App\Form;

use App\Entity\Formation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType; 
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class,['label'=>'Titre de la Formation','label_attr'=>['class'=>'font-weight-bold']])
            ->add('start_date',DateType::class, ['label'=>'Date de début','widget'=>'single_text','label_attr'=>['class'=>'font-weight-bold']])
            ->add('end_date',DateType::class, ['label'=>'Date de fin','widget'=>'single_text','label_attr'=>['class'=>'font-weight-bold']])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
