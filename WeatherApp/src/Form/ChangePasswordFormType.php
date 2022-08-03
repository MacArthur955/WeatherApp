<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordFormType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'required' => false,
                    'label' => 'form.label.newPassword',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'textInput',
                    ],
                    'constraints' => [
                        new NotBlank(message: 'user.notBlank'),
                        new Length(
                            min: 6,
                            minMessage: 'user.password.min',
                            max: 4096,
                        ),
                    ],
                ],
                'second_options' => [
                    'required' => false,
                    'label' => 'form.label.repeatPassword',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'textInput',
                    ],
                ],
                'invalid_message' => 'user.invalid_message',
                'mapped' => false,
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([]);
    }
}
