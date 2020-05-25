<?php

namespace App\Form;

use App\Entity\ProjectForfaitLivrables;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType; 
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class ProjectForfaitLivrablesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('livrable',TextType::class,['label'=>' Titre Du Livrable','attr'=>['class'=>'col-sm-11 text-center'],'label_attr'=>['class'=>'font-weight-bold']])
            ->add('date',DateType::class,['label'=>'date due','widget'=>'single_text','attr'=>['class'=>'col-sm-11  text-center'],'label_attr'=>['class'=>'font-weight-bold']])
            ->add('montant',IntegerType::class,['label'=>'montant a cette date','attr'=>['class'=>'col-sm-11 text-center'],'label_attr'=>['class'=>'font-weight-bold']])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjectForfaitLivrables::class,
        ]);
    }
}
