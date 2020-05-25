<?php

namespace App\Form;

use App\Entity\MonthlySummary;
use Symfony\Component\Form\AbstractType;
use App\Form\ProjectDaysType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class MonthlySummaryType extends AbstractType
{
    /**
     * Permet d'avoir la configuration d'un chanmp
     *
     * @param string $label
     * @param string $placeholder
     * @return array $options
     * @return array
     */
    
     private function getConfiguration($label, $placeholder, $options = []) {
        return array_merge([
            'label' => $label,
            'attr'=>[
                    'placeholder'=>$placeholder
                ]
        ], $options);
    }
    
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder 
        ->add('month',ChoiceType::class ,[
            'label'=>'Mois de Declaration','label_attr'=>['class'=>'font-weight-bold'],
            'choices'=>[
                'Janivier'=>1,
                'Fevrier'=>2,
                'Mars'=>3,
                'Avril'=>4,
                'Mai'=>5,
                'Juin'=>6,
                'Juillet'=>7,
                'Aout'=>8,
                'Septembre'=>9,
                'Octobre'=>10,
                'Novembre'=>11,
                'Decembre'=>12
            ],
            'placeholder' => 'Saisir le mois en cours'
        ]) 
            ->add('projectDays',CollectionType::class,[
                'entry_type'=>ProjectDaysType::class,
                'entry_options' => ['label' => false],
                'allow_add'=>true,
                'by_reference'=>false,
                'label'=>false
                

            ]);
            
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MonthlySummary::class,
        ]);
    }
}
