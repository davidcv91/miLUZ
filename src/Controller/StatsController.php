<?php
namespace App\Controller;

use App\Entity\Consumption;
use App\Entity\DateRange;
use App\Service\ConsumptionStatsHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StatsController extends AbstractController
{
    const PRICE_KWH_EURO = 0.14;

    /**
     * @Route("/stats/insights", name="view_insights")
     */
    public function insights(Request $request, SessionInterface $session, ValidatorInterface $validator, ConsumptionStatsHelper $consumptionStatsHelper)
    {
        $dateRange = $this->getInsightsDateRange($request, $session, $validator);

        $repository = $this->getDoctrine()->getRepository(Consumption::class);
        $consumptionCollection = $repository->findByDatetimeInterval(
            $dateRange->getStartDatetime(),
            $dateRange->getEndDatetime()
        );

        $totalConsumption = $consumptionStatsHelper->getTotalConsumption($consumptionCollection);
        $amountConsumptionEuros = $totalConsumption*self::PRICE_KWH_EURO;
        $aggregatedConsumptionDayOfWeek = $consumptionStatsHelper->getAggregatedConsumptionByDayOfWeek($consumptionCollection);
        $aggregatedConsumptionHour = $consumptionStatsHelper->getAggregatedConsumptionByHour($consumptionCollection);

        $aggregatedConsumptionHourSortDesc = $aggregatedConsumptionHour;
        arsort($aggregatedConsumptionHourSortDesc);

        $twoConsecutiveHoursWithGreaterConsumption = $consumptionStatsHelper->findTwoConsecutiveHoursWithGreaterConsumption($aggregatedConsumptionHourSortDesc);

        $consumptionDuring50HoursWithGreaterConsumption = $consumptionStatsHelper->consumptionDuring50HoursWithGreaterConsumption($consumptionCollection);

        $consumptionInTempoSolar = $consumptionStatsHelper->consumptionInTempoSolar($consumptionCollection);

        return $this->render('consumption/insights.html.twig', [
            'total_consumption' => $totalConsumption,
            'amount_consumption_euros' => $amountConsumptionEuros,
            'aggregated_consumption_dow' => $aggregatedConsumptionDayOfWeek,
            'aggregated_consumption_hour' => $aggregatedConsumptionHour,
            'aggregated_consumption_hour_sort_desc' => $aggregatedConsumptionHourSortDesc,
            'two_consecutive_hours_greater_consumption' => $twoConsecutiveHoursWithGreaterConsumption,
            'consumption_during_50_hours_greater_consumption' => $consumptionDuring50HoursWithGreaterConsumption,
            'consumption_tempo_solar' => $consumptionInTempoSolar,
            'discount_tempo_solar_consumption' => $consumptionInTempoSolar*self::PRICE_KWH_EURO,
        ]);
    }


    /**
     * @Route("/stats/by_day_ajax", name="day_stats_ajax")
     */
    public function day(Request $request, ValidatorInterface $validator, ConsumptionStatsHelper $consumptionStatsHelper)
    {
        $isAjax = $request->isXmlHttpRequest();
        if (!$isAjax) return new JsonResponse(null);

        $dayDate = $request->request->get('day_date');
        $errors = $this->validateDate($dayDate, $validator);

        if (count($errors) > 0) {
            return new JsonResponse([
                'result' => FALSE,
                'error_message' => 'Invalid date'
            ]);
        }

        $datetimeFrom = \DateTime::createFromFormat('Y-m-d', $dayDate)
            ->setTime(0, 0);
        $datetimeTo = \DateTime::createFromFormat('Y-m-d', $dayDate)
            ->setTime(23, 59);

        $repository = $this->getDoctrine()->getRepository(Consumption::class);
        $consumptionCollection = $repository->findByDatetimeInterval($datetimeFrom, $datetimeTo);

        return new JsonResponse([
            'result' => $consumptionStatsHelper->getAggregatedConsumptionByHour($consumptionCollection)
        ]);
    }

    private function getInsightsDateRange(Request $request, SessionInterface $session, ValidatorInterface $validator) : DateRange
    {
        //Define default values
        $datetimeFrom = (new \DateTime())
            ->modify('-1 month')
            ->setTime(0, 0);

        $datetimeTo = (new \DateTime())
            ->setTime(23, 59);

        //Check if POST has data
        if ($request->request->has('date_range_start') OR $request->request->has('date_range_end')) {
            $dateFrom = $request->request->get('date_range_start');
            $errors = $this->validateDate($dateFrom, $validator);

            $dateTo = $request->request->get('date_range_end');
            $errors = $this->validateDate($dateTo, $validator);

//            if (count($errors) > 0) {
//                return new JsonResponse([
//                    'result' => FALSE,
//                    'error_message' => 'Invalid date interval'
//                ]);
//            }

            $datetimeFrom = \DateTime::createFromFormat('Y-m-d', $dateFrom)
                ->setTime(0, 0);
            $datetimeTo = \DateTime::createFromFormat('Y-m-d', $dateTo)
                ->setTime(23, 59);
        }
        else if ($session->has('datetimeFrom') OR $session->has('datetimeTo')) {
            $datetimeFrom = $session->get('datetimeFrom');
            $datetimeTo = $session->get('datetimeTo');
        }


//        if ($datetimeFrom > $datetimeTo) {
//            return new JsonResponse([
//                'result' => FALSE,
//                'error_message' => 'Start date must be earlier than end date'
//            ]);
//        }

        $session->set('datetimeFrom', $datetimeFrom);
        $session->set('datetimeTo', $datetimeTo);

        return new DateRange($datetimeFrom, $datetimeTo);
    }

    /**
     * @param string $date
     * @param ValidatorInterface $validator
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    private function validateDate($date, ValidatorInterface $validator) {
        return $validator->validate(
            $date,
            [
                new Assert\NotBlank(),
                new Assert\Date()
            ]
        );
    }
}