<?php
namespace App\Controller;

use App\Entity\Consumption;
use App\Service\ConsumptionStatsHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="view_dashboard")
     */
    public function index(SessionInterface $session, ConsumptionStatsHelper $consumptionStatsHelper)
    {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_login');
        }

        $datetimeFrom = (new \DateTime('midnight'))
            ->modify("-10 days");
        $datetimeTo = new \DateTime();

        $repository = $this->getDoctrine()->getRepository(Consumption::class);
        $consumptionCollection = $repository->findByDatetimeInterval($datetimeFrom, $datetimeTo);

        return $this->render('dashboard/dashboard.html.twig', [
            'consumption_last_days' => $consumptionStatsHelper->getAggregatedConsumptionByDate($consumptionCollection),
            'current_date' => (new \DateTime())->format('Y-m-d')
        ]);
    }
}