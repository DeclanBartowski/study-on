<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Service\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/transactions')]
final class TransactionController extends AbstractController
{
    #[Route(name: 'app_transactions', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(
        TransactionService $transactionService,
        CourseRepository $courseRepository
    ): Response {

        $transactions = $transactionService->getUserTransactions($this->getUser());
        $courses = $courseRepository->findAll();
        $coursesHandled = [];
        foreach ($courses as $course) {
            $coursesHandled[$course->getSymbolCode()] = [
                'name' => $course->getName(),
                'id' => $course->getId(),
            ];
        }
        $transactions = $transactionService->handlingTransactions($transactions, $coursesHandled);

        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactions,
        ]);
    }
}
