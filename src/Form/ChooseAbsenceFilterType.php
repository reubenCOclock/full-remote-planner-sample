<?php

namespace App\Form;

use App\Entity\FilterConge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ChooseAbsenceFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           
            ->add('consultant',EntityType::class,['class'=>User::class,'query_builder'=>function(EntityRepository $er){
                return $er->createQueryBuilder('u')->join('u.role','r')->where('r.roleTitle=:role_consultant')->setParameter('role_consultant','ROLE_CONSULTANT');
            },'choice_label'=>'firstname','label_attr'=>['class'=>'font-weight-bold']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FilterConge::class,
        ]);
    }
}
