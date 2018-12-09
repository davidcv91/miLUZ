<?php
namespace App\Controller;

use App\Entity\Consumption;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ViewerController extends AbstractController
{
    /**
     * @Route("/view/last_days/{numDays}", name="view_last_days", requirements={"numDays"="\d+"})
     */
    public function index($numDays = 10)
    {
        $datetimeFrom = (new \DateTime('midnight'))
            ->modify("-$numDays days");
        $datetimeTo = new \DateTime();

        $repository = $this->getDoctrine()->getRepository(Consumption::class);
        $consumptions = $repository->findByDatetimeInterval($datetimeFrom, $datetimeTo);

        $result = [];
        foreach ($consumptions->getIterator() as $i => $consumption) {
            $date = $consumption->getDatetime()->format('Y-m-d');
            $result[$date][] = $consumption;
        }

        return $this->render('viewer/viewer.html.twig', [
            'consumptions' => $result
        ]);
    }


}