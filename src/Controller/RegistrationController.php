<?php

namespace App\Controller;

use App\Form\RegistrationFormType;
use App\Security\Authenticator;
use App\Security\User;
use App\Service\BillingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;

class RegistrationController extends AbstractController
{

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        BillingClient $billingClient,
        AuthenticatorInterface $authenticator,
        UserAuthenticatorInterface $userAuthenticator
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $response = $billingClient->post('/api/v1/register', [
                'email' => $formData->getEmail(),
                'password' => $formData->getPassword(),
            ]);

            if (isset($response['errors'])) {
                $this->addFlash('error', implode(', ', $response['errors']));
                return $this->render('registration/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }

            $user = new User();
            $user->setApiToken($response['token']);
            $user->setRoles($response['roles']);
            $user->setEmail($formData->getEmail());

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
