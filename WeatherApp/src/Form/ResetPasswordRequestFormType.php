<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class ResetPasswordRequestFormType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options): void {
        $builder
            ->add('email', TextType::class, [
                'required' => false,
                'attr' => [
                    'autocomplete' => 'email',
                    'class' => 'textInput',
                ],
                'constraints' => [
                    new NotBlank(message: 'user.notBlank'),
                    new Email(message: 'user.email.valid'),
                ],
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([]);
    }
}
?>