<?php

    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\Routing\Annotation\Route;
    use Doctrine\Common\Persistence\ObjectManager;
    use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
    use App\Entity\User;
    use App\Entity\Role;
    use App\Entity\PasswordUpdate;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpFoundation\Request; 
    use App\Entity\Vacation;
    use App\Form\VacationType;
    use App\Entity\Formation;
    use App\Form\FormationType;
    use App\Entity\Project;
    use App\Form\ProjectType;
    use App\Form\UserClientType;
    use App\Form\UserRHType;
    use App\Form\ChooseAbsenceFilterType;
    use App\Form\ViewFacturesByMonthType;
    use App\Utils\Slugger;
    use App\Entity\Feedback;
    use App\Entity\SickDay;
    use App\Form\AccountType;
    use App\Entity\MonthlySummary;
    use App\Form\FacturationFormType;
    use App\Form\UploadFileType;
    use App\Form\EmptyRecapConsultantType;
    use App\Entity\MyDocument;
    use App\Entity\FilterConge;
    use App\Form\PasswordUpdateType;
    use Doctrine\Common\Collections\Collection;
    use Dompdf\Dompdf;
    use Dompdf\Options;
    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\JsonResponse;

    class AdminDocumentController extends AdminUtilsController {

        /**
         * @Route("admin/{slug}/uploadForm", name="uploadPDFFile")
         */

        public function uploadConsultantDocument(Request $request){ 

            $em=$this->getDoctrine()->getManager();

            $user=$this->getUser();
            // creation du document a chargé
            $document=new myDocument();
            
            $form=$this->createForm(UploadFileType::class,$document);

            $form->handleRequest($request);

        

            if($form->isSubmitted() && $form->isValid()){
                // recuperation du fichier chargé 
                $pdfContent=$form['document']->getData();

              
                    $fileName = md5(uniqid()).'.'.$pdfContent->guessExtension();
                       // Move the file to the directory where brochures are stored
                      $pdfContent->move(
                      $this->getParameter('doc_directory'),
                      $fileName);
                     
                // permettre de definir la route pour recuperer le document
                 $document->setUrl($this->getParameter('doc_directory').'/'.$fileName);
                
                 $em->persist($document);
                 
                 $em->flush();

                 $this->addFlash('success','votre document a bien été ajouté');

                 return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);



            
            }

            return $this->render('admin/upload_consultant_document.html.twig',['form'=>$form->createView()]);
        } 
        
        /**
         * @Route("admin/{slug}/seeArretMaladieDocumentsByConsultant", name="seeArretMaladieDocumentsByConsultant")
         */

        public function seeArretMaladieDocumentByConsultant(Request $request){
            // creation d'une entité qui a pour seul but de pouvoir etre lié a un consultant pour pouvoir recupere le consultant d'un formualire
            $filterConge=new FilterConge();

            $em=$this->getDoctrine()->getManager();

            $form=$this->createForm(ChooseAbsenceFilterType::class,$filterConge);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                
                $consultantId=intval($form->get('consultant')->getViewData()); 

            

                $documentRepo=$em->getRepository(MyDocument::class);

                $consultantRepo=$em->getRepository(User::class);

                $consultant=$consultantRepo->findBy(['id'=>$consultantId]);

                $consultantAMDocs=$documentRepo->findBy(['consultant'=>$consultantId,'category'=>'attestation arret maladie']);

                return $this->render('admin/see_document_arret_maladie.html.twig',['consultantAMDocs'=>$consultantAMDocs,'consultant'=>$consultant]);

            } 

            return $this->render('admin/select_document_am.html.twig',['form'=>$form->createView()]);

            
        }  

        /**
         * @Route("admin/{slug}/downloanFacture/{id}", name="downloadFacture")
         */
        
         // fonction generique pour consulter un document d'un consultant
        public function downloadBill($id){
            $em=$this->getDoctrine()->getManager();
            
            $documentRepo=$em->getRepository(MyDocument::class);
            $document=$documentRepo->find($id);
           
            

            $response = new Response();
            $response->setContent(file_get_contents($document->getUrl()));
            $response->headers->set(
                'Content-Type',
                'application/pdf'
            ); // Affiche le pdf au lieux de le télécharger
            $response->headers->set('Content-disposition', 'filename=' . $document->getTitle());
      
            return $response;
        } 

        /**
         * @Route("admin/{slug}/downloadAMAttestaion/{id}", name="downloadAMAttestation")
         */

         // fonction generique pour consulter un document d'un consultant
        
        public function downloadAttestation($id){
            $em=$this->getDoctrine()->getManager();
            
            $documentRepo=$em->getRepository(MyDocument::class);
            $document=$documentRepo->find($id);
            
            $response = new Response();
            $response->setContent(file_get_contents($document->getUrl()));
            $response->headers->set(
                'Content-Type',
                'application/pdf'
            ); // Affiche le pdf au lieux de le télécharger
            $response->headers->set('Content-disposition', 'filename=' . $document->getTitle());
      
            return $response;
        }

        
    }
  



?>