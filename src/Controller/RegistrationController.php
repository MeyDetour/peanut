<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Payment;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\SecurityAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
  /*  public function __construct(private EmailVerifier $emailVerifier)
    {
    }*/

    #[Route('/account/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            $cats = [
                ['type' => 'outcome',
                    'name' => 'Default outcome'],
                ['type' => 'income',
                    'name' => 'Default income'],
                ['type' => 'outcome',
                    'name' => 'Loyer',
                    'montant' => 500]    ,

            ];
            foreach ($cats as $cat) {
                $c = new Category();
                $c->setName($cat['name']);
                $c->setType($cat['type']);
                $c->setOwner($user);
                if (isset($cat['montant'])) {
                    $c->setMontant($cat['montant']);
                }
                $entityManager->persist($c);
            }

            $payments = [
                ['type' => 'cash',
                    'name' => 'Default Cash'],
                ['type' => 'card',
                    'name' => 'Default Credit Card']
            ];
            foreach ($payments as $met) {
                $p = new Payment();
                $p->setName($met['name']);
                $p->setType($met['type']);
                $p->setOwner($user);
                $entityManager->persist($p);
            }
            $entityManager->flush();
            // generate a signed url and email it to the user
         /*   $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('contact@meydetour.com', 'Mey Detour'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );*/

            // do anything else you need here, like send an email

            return $security->login($user, SecurityAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

  /*  #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }*/
}
