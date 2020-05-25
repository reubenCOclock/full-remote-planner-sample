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

class FacturationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
       
        $builder
      /*
        ->add('consultants',EntityType::class,['class'=>USER::class,
        'multiple'=>true,
        'expanded'=>false,
        'query_builder'=>function(EntityRepository $repo){
            return $repo->createQueryBuilder('u')->join('u.role','r')->where('r.roleTitle=:role_consultant')->setParameter('role_consultant','ROLE_CONSULTANT');
        },'choice_label'=>'slug']);
        */

        
    
        ->add('month',ChoiceType::class ,[
            'label'=> 'Mois de déclaration','label_attr'=>['class'=>'font-weight-bold'],
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
            'placeholder' => 'Saisir le mois pour lequel vous souhatierai facturer'
        ])
        ->add('year',IntegerType::class,['label'=>'Choissisez l\'anée Que Vous Souhaiterai Facturer','label_attr'=>['class'=>'font-weight-bold']]
                
            
        );
            
     }
            
        
    

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}
