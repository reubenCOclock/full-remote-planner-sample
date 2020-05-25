<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType; 
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
        $now=new \DateTime();
        $yearsPassed=$now->getTimeStamp()/31536000;
        $actualYear=$yearsPassed +1970;
    
        $year=$now->format('20y');
        $builder
            
            ->add('firstname', TextType::class,['label'=>'Prénom','label_attr'=>['class'=>'font-weight-bold']])
            ->add('lastname', TextType::class, ['label'=>'Nom','label_attr'=>['class'=>'font-weight-bold']]) 
            ->add('role', EntityType::class,['class'=> Role::class,'query_builder'=>function(EntityRepository $er){
                return $er->createQueryBuilder('r')->where('r.roleTitle = :role_consultant')->setParameter('role_consultant','ROLE_CONSULTANT');
            },'choice_label'=>'roleTitle'])
            ->add('birthday',DateType::class,['label'=>'Date de naissance','years'=>range('1970',$actualYear),'widget'=>'single_text','label'=>'birthday','label_attr'=>['class'=>'font-weight-bold']]) 
            ->add('probation_period',DateType::class,['label'=>"Date de fin de période d'essai",'years'=>range('1970',$actualYear),'widget'=>'single_text'])
            ->add('contractual_status',ChoiceType::class, [
                'choices'=>[
                    'cdd'=>'cdd',
                    'stagaire'=>'stagaire',
                    'cdi_periode_essaye'=>'cdi_periode_essaye',
                    'cdi'=>'cdi',
                    'label'=>'statut contractuel',
                    'label_attr'=>['class'=>'font-weight-bold']
                ]
            ])
            ->add('imageFile', VichImageType::class, ['label'=>'Avatar', 'required' => false,
            'allow_delete' => false,
            'download_label' => 'test',
            'download_uri' => false,
            'image_uri' => false ,'label'=>'avatar','label_attr'=>['class'=>'font-weight-bold']])
            ->add('email',EmailType::class,['label'=>'email','label_attr'=>['class'=>'font-weight-bold']])
            ->add('password',PasswordType::class, ['label'=>'Mot de passe','label_attr'=>['class'=>'font-weight-bold']])
            ->add('phone_number',TextType::class,['label'=>'Portable','constraints'=>new Length(['min'=>10])])
            ->add('adress', TextType::class, ['label'=>'Adresse','label_attr'=>['class'=>'font-weight-bold']])
           
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}