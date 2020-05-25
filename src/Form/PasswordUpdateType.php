<?php

namespace App\Form;
use App\Entity\PasswordUpdate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class PasswordUpdateType extends AbstractType
{
    
    /**
     * Permet d'avoir la configuration d'un chanmp
     *
     * @param string $label
     * @param string $placeholder
     * @return array $options
     * @return array
     */
    private function getConfiguration($label, $placeholder, $options = []) {
        return array_merge([
            'label' => $label,
            'attr'=>[
                    'placeholder'=>$placeholder
                ]
        ], $options);
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {   
        $builder
            ->add('oldPassword', PasswordType::class, $this->getConfiguration("Ancien mot de passe","Saisier le mot de passe actuel"))
            ->add('newPassword', PasswordType::class, $this->getConfiguration("Nouveau mot de passe","Saisier le mot de passe actuel"))
            ->add('confirmPassword', PasswordType::class, $this->getConfiguration("Confirmer le mouveau mot de passe","Saisier le mot de passe actuel"))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PasswordUpdate::class,
        ]);
    }
}
