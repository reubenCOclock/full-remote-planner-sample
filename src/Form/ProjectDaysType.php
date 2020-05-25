<?php

namespace App\Form;

use App\Entity\ProjectDays;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Entity\Project;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class ProjectDaysType extends AbstractType
{ 
    private $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
       
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
       
        $builder
            ->add('days',IntegerType::class,['label'=>'Jours Travaillés','label_attr'=>['class'=> 'font-weight-bold']])
            ->add('project',EntityType::class,['label'=>'Projet','label_attr'=>['class'=>'font-weight-bold'],'class'=>Project::class,

            'query_builder'=>function($er){
                return $er->createQueryBuilder('p')->where(':currentUser MEMBER OF p.consultants')->setParameter('currentUser',$this->user)->andWhere('p.isActive=:true')->setParameter('true',true);
            },'choice_label'=>'title'])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjectDays::class,
        ]);
    }
}
