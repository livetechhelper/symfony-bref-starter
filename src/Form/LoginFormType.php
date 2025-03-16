<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'you@example.com',
                    'autocomplete' => 'email',
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('_remember_me', CheckboxType::class, [
                'label' => 'Remember me',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
        ]);
    }

    public function getBlockPrefix(): string
    {
        // Return an empty string to avoid the form name being added as a prefix to form fields
        return '';
    }
} 