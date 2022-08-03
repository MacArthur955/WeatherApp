<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Security\LoginAuthenticator;
use JsonException;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Repository\CitiesRepository;

class RegistrationController extends AbstractController {
    private EmailVerifier $emailVerifier;
    public function __construct(EmailVerifier $emailVerifier) {
        $this->emailVerifier = $emailVerifier;
    }
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        UserAuthenticatorInterface $authenticatorManager,
        LoginAuthenticator $authenticator,
        CitiesRepository $citiesRepository): Response {
        if ($this->isGranted('ROLE_USER')) throw $this->createNotFoundException();
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                ));
            $cities = $citiesRepository->findBy(['def' => true]);
            foreach ($cities as $city) {
                $user->addCity($city);
                $city->setHasUser(true);
            }
            $user->setUserCity($cities[0]->getId());
            $user->setLocale($request->getLocale());
            $entityManager->persist($user);
            $entityManager->flush();
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from('Adam@WeatherApp.com')
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmationEmail.html.twig'));
            $authenticatorManager->authenticateUser($user, $authenticator, $request);
            return $this->render('piaskownica.html.twig');
            #return $this->redirectToRoute('homepage');
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response {
        $locale = $request->getLocale();
        $verify_email_info = [
            'en' => 'Please log in before confirming your email',
            'pl' => 'Proszę zaloguj się przed potwierdzeniem email',
        ];
        $verify_email_success = [
            'en' => 'Your email address has been verified.',
            'pl' => 'Twój adres email został zweryfikowany',
        ];
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            try {
                $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
            }
            catch (VerifyEmailExceptionInterface $exception) {
                $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
                return $this->redirectToRoute('app_register');
            }
        }
        else {
            $this->addFlash('verify_email_info', $verify_email_info[$locale]);
            return $this->redirectToRoute('homepage');
        }
        $this->addFlash('verify_email_success', $verify_email_success[$locale]);
        return $this->redirectToRoute('homepage');
    }
}
