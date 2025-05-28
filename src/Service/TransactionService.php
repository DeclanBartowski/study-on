<?php

namespace App\Service;

use App\Entity\Course;

class TransactionService
{
    public const OPERATION_TYPE_DEPOSIT = 1; // Начисление
    public const OPERATION_TYPE_PAYMENT = 2; // Списание

    public function __construct(protected BillingClient $billingClient)
    {
    }

    public function getUserTransactions($user): array
    {
        $this->billingClient->setHeaders([
            'Authorization: Bearer ' . $user->getApiToken()
        ]);
        $transactions = $this->billingClient->post('/api/v1/transactions');

        return $transactions;
    }

    public function isCourseBuyed(Course $course, array $transactions): bool
    {
        foreach ($transactions as $transaction) {
            if ($transaction['course_code'] !== $course->getSymbolCode()) {
                continue;
            }

            if ($transaction['type'] == self::OPERATION_TYPE_PAYMENT && $transaction['amount'] > 0) {
                return true;
            }
        }

        return false;
    }

    public function isCoursePay(Course $course): bool
    {
        $courseBilling = $this->billingClient->get('/api/v1/courses/' . $course->getSymbolCode());

        if ($courseBilling['type'] == Course::TYPE_FULL) {
            return true;
        }
        return false;
    }

    public function handlingTransactions(array $transactions = [], array $courses = [])
    {
        foreach ($transactions as &$transaction) {
            if ($transaction['type'] == self::OPERATION_TYPE_PAYMENT) {
                $transaction['name'] = 'Покупка';
            } else {
                $transaction['name'] = 'Списание';
            }
            if (isset($transaction['course_code']) && $courses[$transaction['course_code']]) {
                $transaction['course_id'] = $courses[$transaction['course_code']]['id'];
                $transaction['course_name'] = $courses[$transaction['course_code']]['name'];
            }
        }
        return $transactions;
    }

}
