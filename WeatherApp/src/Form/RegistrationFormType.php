<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationFormType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('email', options: [
                'required' => false,
                'attr' => ['class' => 'textInput'],
                'constraints' => [
                    new NotBlank(message: 'user.notBlank'),
                    new Email(message: 'user.email.valid'),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'required' => false,
                'label' => 'form.label.agreeTerms',
                'mapped' => false,
                'attr' => ['class' => 'checkboxInput'],
                'constraints' => [
                    new IsTrue(message: 'user.acceptTerms'),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => false,
                'label' => 'form.label.password',
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'textInput',
                ],
                'constraints' => [
                    new NotBlank(message: 'user.notBlank'),
                    new Length(min: 6, minMessage: 'user.password.min', max: 4096),
                ],
            ])
            ->add('username', options: [
                'required' => false,
                'label' => 'form.label.username',
                'attr' => ['class' => 'textInput'],
                'constraints' => [
                    new NotBlank(message: 'user.notBlank'),
                ],
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
