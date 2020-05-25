<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\ProjectForfaitLivrablesType;
use App\Form\ConsultantProjectPricesRegieType;



class ProjectType extends AbstractType
{ 

    public function buildForm(FormBuilderInterface $builder, array $options)
    {  
        
        $builder
            ->add('title',TextType::class,['label'=>'Titre','label_attr'=>['class'=>'font-weight-bold']])
            ->add('description',TextType::class,['label'=>'Description','label_attr'=>['class'=>'font-weight-bold']])
            ->add('client',EntityType::class,['class'=>User::class,'query_builder'=>function(EntityRepository $repo){
                return $repo->createQueryBuilder('u')->join('u.role','r')->where('r.roleTitle=:role_client')->setParameter('role_client','ROLE_CLIENT')->andWhere('u.isEmployed=:employed')->setParameter('employed',true);
            },'choice_label'=>'companyName'])
           
            ->add('consultants',EntityType::class,['class'=>User::class,
            'multiple'=>true,
            'expanded'=>false,
            'query_builder'=>function(EntityRepository $repo){
                return $repo->createQueryBuilder('u')->join('u.role','r')->where('r.roleTitle=:role_consultant')->setParameter('role_consultant','ROLE_CONSULTANT');
            },'choice_label'=>'slug'])

            
                
            
            ->add('projectForfaitLivrables',CollectionType::class,[
                'entry_type'=>ProjectForfaitLivrablesType::class ,
                'allow_add'=>true,
                'by_reference'=>true,
                'entry_options'=>['attr'=>['class'=>'row']],
                'label'=>false


            ])
            
            
            
        
            ->add('consultantProjectPricesRegies',CollectionType::class,[
                'entry_type'=>ConsultantProjectPricesRegieType::class,
                'allow_add'=>true,
                'by_reference'=>true,
                'entry_options'=>['attr'=>['class'=>'row']],
                'label'=>false
            ])
            
            
            
            ->add('regie',SubmitType::class,['label'=>'Regie','attr'=>['class'=>'btn btn-dark bg-vbl rounded-pill col']])
            ->add('intercontrat',SubmitType::class,['label'=>'InterContrat','attr'=>['class'=>'btn btn-dark bg-vbl rounded-pill col']])
            ->add('forfait',SubmitType::class,['label'=>'Forfait','attr'=>['class'=>'btn btn-dark bg-vbl rounded-pill col']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class
            
        ]);
    }
}
