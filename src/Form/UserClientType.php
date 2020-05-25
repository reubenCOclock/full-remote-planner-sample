<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Role;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType; 
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class UserClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
        $now=new \DateTime();
        $yearsPassed=$now->getTimeStamp()/31536000;
        $actualYear=$yearsPassed +1970;
    
        $year=$now->format('20y');
        $builder
            ->add('companyName',TextType::class,['label'=>'nom de l\'entreprise','label_attr'=>['class'=>'font-weight-bold']])
            ->add('lastname',TextType::class,['label'=>'nom','label_attr'=>['class'=>'font-weight-bold']])
            ->add('firstname',TextType::class,['label'=>'prenom','label_attr'=>['class'=>'font-weight-bold']])
            ->add('role',EntityType::class,['class'=>Role::class,'query_builder'=>function(EntityRepository $er){
                return $er->createQueryBuilder('r')->where('r.roleTitle =:role_client')->setParameter('role_client','ROLE_CLIENT');
            },'choice_label'=>'roleTitle','label'=>'role','label_attr'=>['class'=>'font-weight-bold']])
            ->add('birthday',DateType::class,['years'=>range('1970',$actualYear),'label'=>'birthday','label_attr'=>['class'=>'font-weight-bold'],'widget'=>'single_text'])
            ->add('imageFile', VichImageType::class, ['label'=>'Avatar', 'required' => false,
            'allow_delete' => false,
            'download_label' => 'test',
            'download_uri' => false,
            'image_uri' => false ,
            'label'=>'avatar','label_attr'=>['class'=>'font-weight-bold']])
            ->add('email',EmailType::class,['label'=>'email','label_attr'=>['class'=>'font-weight-bold']])
            ->add('password',PasswordType::class,['label'=>'MDP','label_attr'=>['class'=>'font-weight-bold']])
            ->add('phoneNumber',TextType::class,['label'=>'numero telephone','label_attr'=>['class'=>'font-weight-bold']])
            ->add('adress',TextType::class,['label'=>'adress','label_attr'=>['class'=>'font-weight-bold']])
            ->add('adresseCodePostal',TextType::class,['label'=>'ville et code postal','label_attr'=>['class'=>'font-weight-bold']])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
