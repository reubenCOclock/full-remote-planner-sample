<?php

namespace App\Form;

use App\Entity\ConsultantProjectPricesRegie;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\ProjectForfaitLivrablesType;


class ConsultantProjectPricesRegieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('consultantFirstName',TextType::class,['label'=>'nom du consultant','label_attr'=>['class'=>'col offset-sm-10 font-weight-bold'],'attr'=>['class'=>'col offset-sm-11','disabled'=>true]])
            ->add('price',TextType::class,['label'=>'prix pour ce consultant','label_attr'=>['class'=>'col offset-sm-10 font-weight-bold'],'attr'=>['class'=>'col offset-sm-11']]);
            
            
            
            
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ConsultantProjectPricesRegie::class,
        ]);
    }
}
