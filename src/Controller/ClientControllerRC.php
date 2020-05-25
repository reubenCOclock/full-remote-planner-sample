<?php

namespace App\Controller;


use App\Entity\Role;
use App\Entity\User;
use App\Entity\Project;
use App\Entity\Feedback;
use App\Entity\PasswordUpdate;
use App\Form\AccountType;
use App\Form\FeedbackType;
use App\Form\PasswordUpdateType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\MyDocument;
use Symfony\Component\Form\FormError;


   class ClientControllerRC extends ClientUtilsController { 
        
        
         /**
         * Permet d'afficher le profil
         *
         * @Route("/client/{slug}/profile",name="account_profile_client")
         * 
         * @return Response
         */
        public function profile_card(User $user) {
            return $this->render('security/profile.html.twig', ['user'=>$user]);
        } 
    
        /**
         * Permet d'afficher et de traiter le formulaire de modification du profil
         *
         * @Route("/client/{slug}/profile-update",name="account_profile_update_client")
         * 
         * @return Response
         */
        public function profile(Request $request, ObjectManager $manager){
            
            $user=$this->getUser();
            
            $form=$this->createForm(AccountType::class, $user);
            
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {
                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Les données du profil ont été modifiées avec succès');

                return $this->redirectToRoute('clientpage',['slug'=>$user->getSlug()]);
            }

            return $this->render('security/profileupdate.html.twig', ['form'=>$form->createView()]);
        }

      /**
         * Permet de changer le mot de passe
         * 
         *  @Route("/client/{slug}/password-update",name="account_password_client")
         * 
         * @return Response
         */
    
        public function updatePassword(Request $request, UserPasswordEncoderInterface $encoder, ObjectManager $manager) {
            $passwordUpdate = new PasswordUpdate();
            $user=$this->getUser();

            $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) { 
                

                if(!sodium_crypto_pwhash_str_verify($user->getPassword(),$passwordUpdate->getOldPassword())){
                    $form->get('oldPassword')->addError(new FormError('Le mot de passe saisi n\'est pas le mot de passe actuel'));
                }
                
               
                
                else { 
                    
                    
                    $newPassword=$passwordUpdate->getNewPassword();
                    $hash = $encoder->encodePassword($user,$newPassword);
                    
                    $user->setPassword($hash);
                    
                    $manager->persist($user);
                    $manager->flush();

                    $this->addFlash('success','Le mot de passe a été mis à jour');

                    return $this->redirectToRoute('clientpage',['slug'=>$user->getSlug()]);

                }
            }

            return $this->render('security/password.html.twig', ['form' => $form->createView()]);

        }


        /**
        * @Route("/client/{slug}/seeProjectsAndConsultants",name="seeProjectsAndConsultants")
        */
       public function seeProjectsAndConsultants(){
          $em=$this->getDoctrine()->getManager();
          $currentUser=$this->getUser();
          $projectRepo=$em->getRepository(Project::class);
          $clientProjects=$projectRepo->findProjectsByClient($currentUser->getId());
          return $this->render('client/view_client_projects.html.twig',['clientProjects'=>$clientProjects]);
          
       } 

       /**
        * @Route("/client/{slug}/giveConsultantFeedback/{consultantId}/{projectId}",name="giveConsultantFeedback")
        */

       public function giveConsultantFeedback(Request $request,$consultantId,$projectId){
            $em=$this->getDoctrine()->getManager();
            $feedback=new Feedback();
            $currentUser=$this->getUser();
            $userRepo=$em->getRepository(User::class);
            $consultant=$userRepo->find($consultantId);
            $projectRepo=$em->getRepository(Project::class);
            $project=$projectRepo->find($projectId);
            $form=$this->createForm(FeedbackType::class,$feedback);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $feedback->setConsultant($consultant);
                $feedback->setProject($project);
                
                $em->persist($feedback);
                $em->flush();
                $this->addFlash('success','Merci votre feedback a bien été enregistré');
                
                return $this->redirectToRoute('clientpage',['slug'=>$currentUser->getSlug()]);
            } 

            return $this->render('client/add_feedback.html.twig',['form'=>$form->createView(),'consultant'=>$consultant,'project'=>$project]);
       } 
       /**
        * @Route("/client/{slug}/viewBills/{id}",name="viewBills")
        */
       public function viewBills($id){
          $currentUser=$this->getUser();
          $em=$this->getDoctrine()->getManager();
          $clientDocRepo=$em->getRepository(MyDocument::class);
          $clientDocs=$clientDocRepo->getUserByDocuments($id);

          $monthArray=[];

          $yearArray=[];

          foreach($clientDocs as $clientDoc){
              $monthArray[]=$this->getActualMonthFromInt($clientDoc->getMonth());

              $yearArray[]=$clientDoc->getYear();

          }
          //dd($clientDocs);
          return $this->render('client/view_bills.html.twig',['clientDocs'=>$clientDocs,'monthArray'=>$monthArray,'yearArray'=>$yearArray]);
       } 

       /**
        * @Route("client/{slug}/viewBill/{id}", name="viewBill")
        */

       public function viewDocument($id){
           $user=$this->getUser();

           $em=$this->getDoctrine()->getManager();

           $docRepo=$em->getRepository(MyDocument::class);

           $document=$docRepo->find($id);

           
        $response= new Response();

        $response->setContent(file_get_contents($document->getUrl()));
        $response->headers->set(
            'Content-Type',
            'application/pdf'
        ); // Affiche le pdf au lieux de le télécharger
        $response->headers->set('Content-disposition', 'filename=' . $document->getTitle());
  
        return $response;
     } 

           

           
}
   