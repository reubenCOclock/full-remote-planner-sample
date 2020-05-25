<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Form\ProjectForfaitLivrablesType;
use App\Form\ConsultantProjectPricesRegieType;

class ProjectRegieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('consultantProjectPricesRegies',CollectionType::class,[
                'entry_type'=>ConsultantProjectPricesRegieType::class,
                'allow_add'=>true,
                'by_reference'=>true,
                'entry_options'=>['attr'=>['class'=>'row'],'label'=>false],
                'label'=>false
                
        ]);
       
            
            
            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
