<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\BillingClient;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(Request $request, BillingClient $billingClient): Response
    {
        $user = $request->getSession()->get('user');
        $error = '';

        if (!$user->getApiToken()) {
            $error = 'Вы не авторизованы';
        }

        $billingClient->setHeaders([
            'Authorization: Bearer ' . $user->getApiToken()
        ]);
        $userInfo = $billingClient->get('/api/v1/users/current');

        $userInfo['main_role'] = 'Пользователь';
        if (in_array('ROLE_SUPER_ADMIN', $userInfo['roles'])) {
            $userInfo['main_role'] = 'Администратор';
        }

        return $this->render('profile/index.html.twig', [
            'user' => $userInfo,
            'error' => $error,
        ]);
    }
}
