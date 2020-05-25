<?php

namespace App\Form;

use App\Entity\MyDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class InitialDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('document_cv',FileType::class,['mapped'=>false,'required'=>false,'label'=>'telecharger votre CV','label_attr'=>['class'=>'font-weight-bold'],
            
        'constraints' => [
            new File([
                'maxSize' => '1024k',
                'mimeTypes' => [
                    'application/pdf',
                    'application/x-pdf',
                ],
                'mimeTypesMessage' => 'Please upload a valid PDF document',
            ])
        ],])

        ->add('document_carte_identite',FileType::class,['mapped'=>false,'required'=>false,'label'=>'telecharger une copie de votre cartre d\identité','label_attr'=>['class'=>'font-weight-bold'],
            
        'constraints' => [
            new File([
                'maxSize' => '1024k',
                'mimeTypes' => [
                    'application/pdf',
                    'application/x-pdf',
                ],
                'mimeTypesMessage' => 'Please upload a valid PDF document',
            ])
        ],])

        ->add('document_rib',FileType::class,['mapped'=>false,'required'=>false,'label'=>'telecharger une copie votre RIB', 'label_attr'=>['class'=>'font-weight-bold'],
            
        'constraints' => [
            new File([
                'maxSize' => '1024k',
                'mimeTypes' => [
                    'application/pdf',
                    'application/x-pdf',
                ],
                'mimeTypesMessage' => 'Please upload a valid PDF document',
              
            ])
        ],])

        ->add('document_navigo',FileType::class,['mapped'=>false,'required'=>false,'label'=>'telecharger une copie de votre Navigo','label_attr'=>['class'=>'font-weight-bold'],
            
        'constraints' => [
            new File([
                'maxSize' => '1024k',
                'mimeTypes' => [
                    'application/pdf',
                    'application/x-pdf',
                ],
                'mimeTypesMessage' => 'Please upload a valid PDF document',
               
            ])
        ],])

        ->add('document_attestation_domicile',FileType::class,['mapped'=>false,'required'=>false,'label'=>'telecharger une copie de votre attestation de domicile','label_attr'=>['class'=>'font-weight-bold'],
            
        'constraints' => [
            new File([
                'maxSize' => '1024k',
                'mimeTypes' => [
                    'application/pdf',
                    'application/x-pdf',
                ],
                'mimeTypesMessage' => 'Please upload a valid PDF document',
            ])
            
        
        ],])
        ->addEventListener(FormEvents::PRE_SUBMIT,[$this, 'testPostSetData']);
        
          
    } 


    public function testPostSetData(FormEvent $event){
        //$data=$event->getData();
        $data=$event->getData();
        $form=$event->getForm();

        foreach($form as $value){
            if($value->getData()!=null){
               dump($value);
            }
        }
       

       

        //die();
        //dd($data);  
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MyDocument::class,
        ]);
    }
}
