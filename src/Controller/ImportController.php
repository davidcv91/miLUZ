<?php
namespace App\Controller;

use App\Entity\ConsumptionImport;
use App\Entity\ConsumptionParser;
use App\Form\ConsumptionImportType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportController extends AbstractController
{
    /**
      * @Route("/import/new", name="new_import")
      */
    public function new(Request $request, ValidatorInterface $validator)
    {
        $import = new ConsumptionImport();
        $form = $this->createForm(ConsumptionImportType::class, $import);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $filePath = $import->getFile()->getPathname();
            $parser = new ConsumptionParser(new \SplFileObject($filePath));
            $consumptionCollection = $parser->getData();

            $entityManager = $this->getDoctrine()->getManager();
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

                // Replace values if exist => ON DUPLICATE KEY ... UPDATE
                $entityManager->merge($consumption); //persist
            }
            $entityManager->flush();
            return $this->redirect($this->generateUrl('view_dashboard'));
        }

        return $this->render('import/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
}