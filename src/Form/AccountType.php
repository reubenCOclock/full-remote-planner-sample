<?php

namespace App\Form;

use App\Entity\User;
use Cocur\Slugify\Slugify;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $now=new \DateTime();
        $yearsPassed=$now->getTimeStamp()/31536000;
        $actualYear=$yearsPassed +1970;
    
        $year=$now->format('20y');
        $builder
            ->add('lastname',TextType::class,['label'=>'Nom de famille'])
            ->add('firstname',TextType::class,['label'=>'Prénom'])
            ->add('ssId')
            ->add('imageFile', VichImageType::class, ['label'=>'Avatar', 'required' => false,
            'allow_delete' => false,
            'download_label' => 'test',
            'download_uri' => false,
            'image_uri' => false ,])
            ->add('email')
            ->add('phone_number',TextType::class,['constraints'=>new Length(['min'=>10])])
            ->add('adress', TextType::class, ['label'=>'Adresse'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
