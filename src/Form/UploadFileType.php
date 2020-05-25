<?php

namespace App\Form;

use App\Entity\MyDocument;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File;


class UploadFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('title',TextType::class,['label'=>'Titre','label_attr'=>['class'=>'font-weight-bold']])
            ->add('category',ChoiceType::class,['label_attr'=>['class'=>'font-weight-bold'],'choices'=>[
                'contrat/avanenat'=>'contrat/avenant',
                'entretien evaluation'=>'entretien evaluation',
                'entretien pro'=>'entretien pro',
                'suivi de mission'=>'suivi de mission'


            ]])
            
            ->add('consultant',EntityType::class,['class'=>User::class,'query_builder'=>function(EntityRepository $er){
                return $er->createQueryBuilder('c')->join('c.role','r')->where('r.roleTitle=:roleTitle')->setParameter('roleTitle','ROLE_CONSULTANT');
            },'choice_label'=>'firstname','label_attr'=>['class'=>'font-weight-bold']])

            ->add('document',FileType::class,['mapped'=>false,'required'=>false,'label_attr'=>['class'=>'font-weight-bold'],
            
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'application/pdf',
                        'application/x-pdf',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PDF document',
                ])
            ],]);
            
            
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyDocument::class,
        ]);
    }
}
