<?php

namespace App\Form;

use App\Entity\SickDay;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType; 
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class SickDayType extends AbstractType
{
    /**
     * Permet d'avoir la configuration d'un chanmp
     *
     * @param string $label
     * @param string $placeholder
     * @return array
     */
    
     private function getConfiguration($label, $placeholder) {
        return [
            'label' => $label,
            'attr' => ['placeholder' => $placeholder
        ]];
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            
            ->add('start_date',DateType::class,['label'=>'Date de debut','widget'=>'single_text','label_attr'=>['class'=>'font-weight-bold']])
            ->add('end_date',DateType::class, ['label'=>'Date de fin','widget'=>'single_text','label_attr'=>['class'=>'font-weight-bold']])
            ->add('document',FileType::class,['mapped'=>false,'required'=>false,'label'=>'Viuellez charger votre document d\'arret maladie en format PDF','label_attr'=>['class'=>'font-weight-bold'],
            
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'application/pdf',
                        'application/x-pdf',
                    ],
                    'mimeTypesMessage' => 'Vieullez charger votre document d\'arret maladie en format PDF',
                    
                ])
            ],]);
          
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SickDay::class,
        ]);
    }
}
