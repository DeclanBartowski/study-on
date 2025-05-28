<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use App\Service\BillingClient;
use App\Service\CourseService;
use App\Service\TransactionService;
use App\Service\UserBillingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/courses')]
final class CourseController extends AbstractController
{
    #[Route(name: 'app_course_index', methods: ['GET'])]
    public function index(
        CourseRepository $courseRepository,
        BillingClient $billingClient,
        CourseService $courseService,
        UserBillingService $userBillingService,
        TransactionService $transactionService
    ): Response {
        $courses = $courseRepository->findAll();
        $coursesBilling = $billingClient->post('/api/v1/courses');
        $mergedCourses = $courseService->mergeCourses($coursesBilling, $courses);

        $userInfo = [];
        if ($user = $this->getUser()) {
            $transactions = $transactionService->getUserTransactions($user);
            $mergedCourses = $courseService->mergeCoursesWithTransactions($mergedCourses, $transactions);

            $userInfo = $userBillingService->getUserInfo($user);
        }

        return $this->render('course/index.html.twig', [
            'courses' => $mergedCourses,
            'user_info' => $userInfo,
        ]);
    }

    #[Route('/new', name: 'app_course_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager, BillingClient $billingClient): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->wrapInTransaction(function () use ($entityManager, $form, $billingClient, $course) {
                $formData = $form->getData();
                $billingData = [
                    'name' => $formData->getName(),
                    'type' => $formData->getType(),
                    'price' => $formData->getPrice(),
                    'code' => $formData->getSymbolCode(),
                ];
                $result = $billingClient->post('/api/v1/courses/new', $billingData);

                if ($result['success']) {
                    $entityManager->persist($course);
                    $entityManager->flush();
                }
            });

            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_show', methods: ['GET'])]
    public function show(
        Course $course,
        BillingClient $billingClient,
        UserBillingService $userBillingService
    ): Response {
        $courseBilling = $billingClient->get('/api/v1/courses/' . $course->getSymbolCode());

        return $this->render('course/show.html.twig', [
            'course' => $course,
            'course_billing' => $courseBilling,
            'lessons' => $course->getLessons()->toArray(),
            'user_info' => $userBillingService->getUserInfo($this->getUser()),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_course_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function edit(
        Request $request,
        Course $course,
        EntityManagerInterface $entityManager,
        BillingClient $billingClient
    ): Response {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->wrapInTransaction(function () use ($entityManager, $form, $billingClient, $course) {
                $formData = $form->getData();
                $billingData = [
                    'name' => $formData->getName(),
                    'type' => $formData->getType(),
                    'price' => $formData->getPrice(),
                ];
                $result = $billingClient->post('/api/v1/courses/' . $course->getSymbolCode() . '/edit', $billingData);
                if ($result['success']) {
                    $entityManager->flush();
                }
            });

            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_course_delete', methods: ['POST'])]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(Request $request, Course $course, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $course->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($course);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/purchase', name: 'app_course_purchase', methods: ['GET'])]
    public function coursePurchase(Course $course, BillingClient $billingClient): RedirectResponse
    {
        if (!$user = $this->getUser()) {
            $this->addFlash('error', 'Вы не авторизованы');
        } else {
            $billingClient->setHeaders([
                'Authorization: Bearer ' . $user->getApiToken()
            ]);
            $payResult = $billingClient->post('/api/v1/courses/' . $course->getSymbolCode() . '/pay');

            if ($payResult['success']) {
                $this->addFlash('success', 'Курс успешно оплачен!');
            } else {
                $this->addFlash('error', $payResult['message']);
            }
        }

        return $this->redirectToRoute('app_course_show', ['id' => $course->getId()]);
    }
}
