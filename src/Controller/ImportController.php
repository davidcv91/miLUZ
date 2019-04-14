<?php
namespace App\Controller;

use App\Entity\Consumption;
use App\Entity\ConsumptionImport;
use App\Entity\ConsumptionParser;
use App\Form\ConsumptionImportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportController extends AbstractController
{
    /**
      * @Route("/import/new", name="new_import")
      */
    public function new(Request $request, ValidatorInterface $validator, SessionInterface $session)
    {
        $import = new ConsumptionImport();
        $form = $this->createForm(ConsumptionImportType::class, $import);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $filePath = $import->getFile()->getPathname();
            $parser = new ConsumptionParser(new \SplFileObject($filePath));
            $consumptionCollection = $parser->getData();

            foreach($consumptionCollection->getIterator() as $i => $consumption) {

                $errors = $validator->validate($consumption);
                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        $this->addFlash(
                            'error',
                            $error->getMessage()
                        );
                    }

                    return $this->redirect($this->generateUrl('new_import'));
                }
            }

            $consumptionCollection = $consumptionCollection->sortByDate();
            $session->set('last_import_data', $consumptionCollection);

            $repository = $this->getDoctrine()->getRepository(Consumption::class);
            $consumptionCollectionDb = $repository->findByDatetimeInterval(
                $consumptionCollection->first()->getDatetime(),
                $consumptionCollection->last()->getDatetime()
            )->indexById();

            return $this->render('import/new.html.twig', [
                'form' => $form->createView(),
                'imported_consumption' => $consumptionCollection,
                'consumption_from_db' => $consumptionCollectionDb
            ]);
        }

        return $this->render('import/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/import/cancel", name="cancel_import")
     */
    public function cancel(SessionInterface $session)
    {
        $session->remove('last_import_data');

        return $this->redirect($this->generateUrl('new_import'));
    }

    /**
     * @Route("/import/process", name="process_import")
     */
    public function process(Request $request, SessionInterface $session)
    {
        $customConsumption = $request->request->get('custom_consumption');
        $consumptionCollection = $session->get('last_import_data');
        $entityManager = $this->getDoctrine()->getManager();

        foreach($consumptionCollection->getIterator() as $consumption) {

            if (isset($customConsumption[$consumption->getId()])) {
                $consumption->setConsumption($customConsumption[$consumption->getId()]);
            }
            // Replace values if exist => ON DUPLICATE KEY ... UPDATE
            $entityManager->merge($consumption); //persist
        }
        $entityManager->flush();
        $session->remove('last_import_data');

        return $this->redirect($this->generateUrl('view_dashboard'));
    }
}