<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
{
    /**
     * @Route("/stats", name="view_stats")
     */
    public function index()
    {
        return $this->render('viewer/stats/stats.html.twig', []);
    }

    /**
     * @Route("/stats/set_date", name="set_date")
     */
    public function setDate(Request $request, SessionInterface $session)
    {
        $dateFrom = $request->request->get('date_from');
        $dateTo = $request->request->get('date_to');

        if (!empty($dateFrom) AND !empty($dateTo)) {
            $datetimeFrom = \DateTime::createFromFormat('Y-m-d', $dateFrom);
            $datetimeTo = \DateTime::createFromFormat('Y-m-d', $dateTo);

            $session->set('datetimeFrom', $datetimeFrom);
            $session->set('datetimeTo', $datetimeTo);
        }

    }
}