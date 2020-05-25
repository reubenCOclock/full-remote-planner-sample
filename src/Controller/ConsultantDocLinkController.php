<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\SickDay;
use App\Entity\Vacation;
use App\Entity\SubVacation;
use App\Entity\Formation;
use App\Entity\Project;
use App\Form\AccountType;
use App\Form\SickDayType;
use App\Entity\MyDocument;
use App\Entity\FilterConge;
use App\Entity\LoadInitialDocuments;
use App\Form\VacationType;
use App\Entity\DocumentHolder;
use App\Form\FormationType;
use App\Form\ProjectDaysType;
use App\Form\InitialDocumentType;
use App\Entity\MonthlySummary;
use App\Entity\PasswordUpdate;
use App\Form\MonthlySummaryType;
use App\Form\PasswordUpdateType;
use App\Form\UploadArretMaladieDocType;
use App\Repository\UserRepository; 
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\FormError;
use App\Entity\ProjectDays;
use App\Entity\WorkingDaysTest;
use App\Controller\ConsultantAMController;
use Symfony\Component\HttpFoundation\Session\Session;


class ConsultantDocLinkController extends ConsultantUtilsController {

    /**
     * @Route("user/{slug}/viewContratsAvenants", name="viewContratsAvenants")
     */
    public function contratsAvenantPage(){

        $user=$this->getUser();

        $em=$this->getDoctrine()->getManager();

        $documentRepository=$em->getRepository(MyDocument::class);

        $consultantAvenantDocuments=$documentRepository->findBy(['consultant'=>$user->getId(),'category'=>'contrat/avenant']);

        return $this->render('consultant/view_contrats_avenant.html.twig',['consultantAvenantDocuments'=>$consultantAvenantDocuments]);
    }

   


    /**
     * @Route("/user/{slug}/viewProInterviews", name="viewProInterviews")
     */
    public function entretiensProfesionnellePage(){
        $em=$this->getDoctrine()->getManager();

        $user=$this->getUser();


        $documentRepository=$em->getRepository(MyDocument::class);

        $entretienProDocuments=$documentRepository->findBy(['consultant'=>$user->getId(),'category'=>'entretien pro']);

        return $this->render('consultant/view_entretien_pro_docs.html.twig',['entretienProDocuments'=>$entretienProDocuments]);
    } 

    /**
     * @Route("user/{slug}/viewEvaluations", name="viewEvaluations")
     */

    public function entretiensEvaluationPage(){
        $em=$this->getDoctrine()->getManager();
        
        $user=$this->getUser();

        $documentRepository=$em->getRepository(MyDocument::class);

        $userEvaluationsDocuments=$documentRepository->findBy(['consultant'=>$user->getId(),'category'=>'entretien evaluation']);

        return $this->render('consultant/view_evaluation_docs.html.twig',['userEvaluationsDocuments'=>$userEvaluationsDocuments]);
    } 


    /**
     * @Route("user/{slug}/suiviMissions", name="suiviMissions")
     */
    public function suiviDeMissions(){
        $em=$this->getDoctrine()->getManager();

        $user=$this->getUser();

        $documentRepository=$em->getRepository(MyDocument::class);

        $userSuiviMissions=$documentRepository->findBy(['consultant'=>$user->getId(),'category'=>'suivi de mission']);

        return $this->render('consultant/view_suivi_mission_docs.html.twig',['userSuiviMissions'=>$userSuiviMissions]);
    }


     /**
     * @Route("user/{slug}/viewDocument/{id}", name="viewDocument")
     */
    public function viewDetailsDocumentsConsultant($id){
        $em=$this->getDoctrine()->getManager();

        $documentRepository=$em->getRepository(MyDocument::class);

        $document=$documentRepository->find($id);

        $response= new Response();

        $response->setContent(file_get_contents($document->getUrl()));
        $response->headers->set(
            'Content-Type',
            'application/pdf'
        ); // Affiche le pdf au lieux de le télécharger
        $response->headers->set('Content-disposition', 'filename=' . $document->getTitle());
  
        return $response;
    } 

    /**
     * @Route("user/{slug}/uploadInitialDocuments", name="uploadInitialDocuments")
     */

    public function uploadInitialDocuments(Request $request){
        $user=$this->getUser();

        $empty=true;
        $em=$this->getDoctrine()->getManager();

        $documentRepo=$em->getRepository(MyDocument::class);

        $consultantInitialDocuments=$documentRepo->findBy(['category'=>'documents initialisation', 'consultant'=>$user->getId()]);

        //dd($consultantInitialDocuments);

        
        

        $form=$this->createForm(InitialDocumentType::class);

        $form->handleRequest($request);

        if(!empty($consultantInitialDocuments)){
            $empty=false;
            if($empty == false){
                return $this->render('consultant/upload_initial_documents.html.twig',['empty'=>$empty,'form'=>$form->createView()]);
            }
        }
        

      

        if($form->isSubmitted() && $form->isValid()){
            $errors=false;
          
            foreach($form as $value){

                $document=new MyDocument();
                $document->setTitle($value->getName());
                $document->setCategory('documents initialisation');
                $document->setConsultant($user);
              


                $pdfContent=$form[$value->getName()]->getData();

               
                if($pdfContent == null){
                    $errors=true;
                    $this->addFlash('warning','il manque votre' .$value->getName().' '.'viellez recharger vos documents');
                }

                else {

                $fileName = md5(uniqid()).'.'.$pdfContent->guessExtension();
                // Move the file to the directory where brochures are stored
               $pdfContent->move(
               $this->getParameter('doc_directory'),
               $fileName);

               $document->setUrl($this->getParameter('doc_directory').'/'.$fileName);
              
               $em->persist($document);
            }
          }
          
          if($errors == false){
           
            $em->flush();
            $this->addFlash('success','merci, vos documents d\'initialisation ont bien été chargé');

            return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
          }
        
        }

        return $this->render('consultant/upload_initial_documents.html.twig',['form'=>$form->createView(),'empty'=>$empty]);

    } 

    /**
     * @Route("user/{slug}/viewInitialDocuments", name="viewInitialDocuments")
     */
    public function showInitialConsultantDocuments(){
        $user=$this->getUser();
        $em=$this->getDoctrine()->getManager();

        $documentRepository=$em->getRepository(MyDocument::class);

        $consultantInitialDocuments=$documentRepository->findBy(['category'=>'documents initialisation', 'consultant'=>$user->getId()]);

        return $this->render('consultant/view_initial_documents.html.twig',['consultantInitialDocuments'=>$consultantInitialDocuments]);

        
    }

    /**
     * @Route("user/{slug}/modifyInitialDocument/{id}", name="modifyInitialDocument")
     */

    public function modifyInitialDocument($id,Request $request) {
        $user=$this->getUser();

        $em=$this->getDoctrine()->getManager();

        $documentRepository=$em->getRepository(MyDocument::class);

        $documentToModify=$documentRepository->find($id);

        $form=$this->createForm(UploadArretMaladieDocType::class,$documentToModify);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $pdfContent=$form['document']->getData();
            


            if($pdfContent == null){
                $errors=true;
                $this->addFlash('warning','viellez bien modifier votre document');
            }

            else {
            

            $fileName = md5(uniqid()).'.'.$pdfContent->guessExtension();
            // Move the file to the directory where brochures are stored
           $pdfContent->move(
           $this->getParameter('doc_directory'),
           $fileName);

           $documentToModify->setUrl($this->getParameter('doc_directory').'/'.$fileName);
          
           $em->persist($documentToModify);


            $em->flush();
           

            $this->addFlash('success','votre document initiale a bien été modifié');

            return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
        } 

    

        }

        return $this->render('consultant/modify_initial_document.html.twig',['form'=>$form->createView()]);


    } 

  
    
   
    
}