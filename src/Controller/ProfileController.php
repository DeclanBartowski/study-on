<?php

namespace App\Controller;

use App\Service\UserBillingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(Request $request, UserBillingService $userBillingService): Response
    {
        $userInfo = $userBillingService->getUserInfo($this->getUser());

        $userInfo['main_role'] = 'Пользователь';
        if (in_array('ROLE_SUPER_ADMIN', $userInfo['roles'])) {
            $userInfo['main_role'] = 'Администратор';
        }

        return $this->render('profile/index.html.twig', [
            'user' => $userInfo,
        ]);
    }
}
