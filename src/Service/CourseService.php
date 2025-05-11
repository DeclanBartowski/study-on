<?php

namespace App\Service;

use App\Entity\Course;

class CourseService
{
    public function mergeCourses(array $coursesData, array $courseEntities): array
    {
        $result = [];
        foreach ($courseEntities as $course) {
            $data['entity'] = $course;
            foreach ($coursesData as $courseData) {
                if ($courseData['code'] == $course->getSymbolCode()) {
                    $data['data'] = $courseData;
                }
            }

            $result[] = $data;
        }

        return $result;
    }

    public function mergeCoursesWithTransactions(array $coursesData, array $transactions): array
    {
        foreach ($coursesData as &$data) {
            foreach ($transactions as $transaction) {
                if (!isset($transaction['course_code']) || $transaction['course_code'] != $data['entity']->getSymbolCode()) {
                    continue;
                }

                switch ($transaction['type']) {
                    case Course::TYPE_FULL:
                        $data['data']['course_bought'] = true;
                        break;
                    case Course::TYPE_RENT:
                        $expiresAt = (new \DateTime())->modify('+' . Course::RENT_TYPE_TIME);
                        if (strtotime($transaction['created_at']) > $expiresAt->getTimestamp()) {
                            $expiredDate = new \DateTime($transaction['created_at']);
                            $data['data']['course_expired'] = $expiredDate->format('d F Y');
                        }
                        break;
                    case Course::TYPE_FREE:
                }
            }
        }

        return $coursesData;
    }
}
