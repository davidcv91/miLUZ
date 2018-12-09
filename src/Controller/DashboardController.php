<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="view_dashboard")
     */
    public function index()
    {
        return $this->render('dashboard/dashboard.html.twig', [

        ]);
    }
}