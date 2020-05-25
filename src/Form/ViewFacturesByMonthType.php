<?php

namespace App\Form;

use App\Entity\MyDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ViewFacturesByMonthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
       
        $builder
     
        ->add('month',ChoiceType::class ,[
            'label'=> 'Mois de déclaration', 'label_attr'=>['class'=>'font-weight-bold'],
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
            'placeholder' => 'Saisir Le Mois'
        ])
        ->add('year',IntegerType::class ,[
            'label'=>'saisier l\'anée sur 4 chiffres','label_attr'=>['class'=>'font-weight-bold']
        ])
            ;
     }
            
        
    

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}
