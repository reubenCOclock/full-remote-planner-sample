<?php

namespace App\Controller;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Role;
use App\Entity\User;
use App\Form\UserType;
use App\Utils\Slugger;
use App\Entity\Vacation;
use App\Entity\Formation;
use App\Entity\SickDay;
use App\Form\AccountType;
use App\Entity\MyDocument;
use App\Form\VacationType;
use App\Form\FormationType; 
use App\Entity\PasswordUpdate;
use App\Entity\FilterConge;
use App\Form\PasswordUpdateType;
use App\Form\ChooseAbsenceFilterType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Session\Session;
    

    
  class RHControllerRC extends RHAbsenceController{ 
        
        /**
         * Permet d'afficher le profil
         *
         * @Route("/rh/{slug}/profile",name="account_profile_rh")
         * 
         * @return Response
         */
        public function profile_card(User $user) {
            return $this->render('security/profile.html.twig', ['user'=>$user]);
        } 
        
        
        /**
         * Permet d'afficher et de traiter le formulaire de modification du profil
         *
         * @Route("/rh/{slug}/profile-update",name="account_profile_update_rh")
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
                return $this->redirectToRoute('rhpage',['slug'=>$user->getSlug()]);
            }
            return $this->render('security/profileupdate.html.twig', ['form'=>$form->createView()]);
        }
        
        /**
         * Permet de changer le mot de passe
         * 
         *  @Route("/rh/{slug}/password-update",name="account_password_rh")
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

                    return $this->redirectToRoute('rhpage',['slug'=>$user->getSlug()]);

                }
            }

            return $this->render('security/password.html.twig', ['form' => $form->createView()]);

        }
        
        
        
        /**
         * @Route("/rh/{slug}/voirDemandeConge", name="voirDemandeConge")
         */
        public function voirDemandeCongeNonValide(){
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();

            $now =new \DateTIme();
            if(!$user->getIsEmployed()){
                throw new \Exception('you are no longer an employee');
            }
            $vacationRepo=$em->getRepository(Vacation::class);
            $vacationsNotValidated=$vacationRepo->findBy(['isValidated'=>false]);

            $this->calculateAbsentWorkingDays(Vacation::class);
            return $this->render('rh/validation_conge.html.twig',['vacations'=>$vacationsNotValidated]);
        } 

        /**
         * @Route("/rh/{slug}/filterDemandeConge", name="filterDemandeConge")
         */
        
        public function filterDemandeConge(Request $request){

         


            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();

            $filterConge=new FilterConge();


            $form=$this->createForm(ChooseAbsenceFilterType::class,$filterConge);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

                $em=$this->getDoctrine()->getManager();
                $userRepo=$em->getRepository(User::class);
                
                $consultantId=intval($form->get('consultant')->getViewData());

                $consultant=$userRepo->findOneBy(['id'=>$consultantId]);
                

            
                $consultantVacations=$this->sortAbsencesByConsultant(Vacation::class,$consultantId);

                $consultantFormations=$this->sortAbsencesByConsultant(Formation::class,$consultantId);

                $consultantSickDays=$this->sortAbsencesByConsultant(SickDay::class,$consultantId);

                $this->calculateAbsentWorkingDays(Vacation::class);
                $this->calculateAbsentWorkingDays(Formation::class);
                $this->calculateAbsentWorkingDays(SickDay::class);

                

                return $this->render('rh/see_absences_by_consultant.html.twig',['consultantVacations'=>$consultantVacations,'consultantFormations'=>$consultantFormations,'consultantSickDays'=>$consultantSickDays,'consultant'=>$consultant]);

                
            }


            return $this->render('rh/filter_absence_view.html.twig',['form'=>$form->createView()]);

            

        }
        /**
         * @Route("/rh/{slug}/valideDemandeConge/{id}", name="valideDemandeConge")
         */
        public function valideUneDemandeConge($id) {
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            if(!$user->getIsEmployed()){
                throw new \Exception('you are no longer an employee');
            }
            $vacationRepo=$em->getRepository(Vacation::class);
            $user=$this->getUser();
            $vacation=$vacationRepo->find($id);
            $vacation->setIsValidated(true);
            $vacation->setRhValidator($user);
            $em->flush();
    
            $this->addFlash('success','Demande de congé validée');
            return $this->redirectToRoute('rhpage',['slug'=>$user->getSlug()]);
        } 
        /**
         * @Route("/rh/{slug}/voirDemandeFormation", name="voirDemandeFormation")
         */
        public function voirDemandeFormation(){
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();

            $now = new \DateTIme();
            if(!$user->getIsEmployed()){
                throw new \Exception('you are no longer an employee');
            }
            $formationRepo=$em->getRepository(Formation::class);
            $formationsNotValidated=$formationRepo->findBy(['isValidated'=>false]);

            return $this->render('rh/validation_formation.html.twig',['formations'=>$formationsNotValidated]);
        } 
        /**
         * @Route("/rh/{slug}/validerDemandeFormation/{id}",name="validerDemandeFormation")
         */
        public function validerUneDemandeDeFormation($id){
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            if(!$user->getIsEmployed()){
                throw new \Exception('you are no longer an employee');
            }
            $formationRepo=$em->getRepository(Formation::class);
            $formation=$formationRepo->find($id);
            $formation->setIsValidated(true);
            $formation->setRhValidator($user);
            $em->flush();
            $this->addFlash('success','Demande de formation validée');
            return $this->redirectToRoute('rhpage',['slug'=>$user->getSlug()]);
        } 
        /**
         * @Route("/rh/{slug}/voirConsultants",name="voirConsultants")
         */
        public function voirLesConsultants(){
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            if(!$user->getIsEmployed()){
                throw new \Exception('you are no longer an employee');
            }
            $userRepo=$em->getRepository(User::class);
            $consultants=$userRepo->filterUsersByRoleConsultant();
            return $this->render('rh/modification_ajout_consultant.html.twig',['consultants'=>$consultants]);
   
        }  
        /**
         * @Route("rh/{slug}/reactiveConsultant/{id}",name="reactivateConsultant")
         */
        public function reactivateConsultant($id){
            $em=$this->getDoctrine()->getManager();
            $currentUser=$this->getUser();
            if(!$currentUser->getIsEmployed()){
                throw new \Exception('you are no longer an employee');
            }
            $userRepo=$em->getRepository(User::class);
            $user=$userRepo->find($id);
            $user->setIsEmployed(true);
            $em->flush();
            $this->addFlash('success','Collaborateur réactivé');
            return $this->redirectToRoute('rhpage',['slug'=>$currentUser->getSlug()]);
        } 
        /**
         * @Route("rh/{slug}/desactivateConsultant/{id}",name="desactivateConsultant")
         */ 
         public function desactivateConsultant($id){
            $em=$this->getDoctrine()->getManager();
            $userRepo=$em->getRepository(User::class);
            $currentUser=$this->getUser();
           
            $user=$userRepo->find($id);
            $user->setIsEmployed(false);
            $em->flush();
            $this->addFlash('danger','Collaborateur désactivé');
            return $this->redirectToRoute('rhpage',['slug'=>$currentUser->getSlug()]);
         } 
         /**
          * @Route("rh/{slug}/addConsultant",name="addConsultant")
          */
         public function addConsultant(Request $request,Slugger $slugger,UserPasswordEncoderInterface $hasher){ 
            $em=$this->getDoctrine()->getManager();
            $currentUser=$this->getUser();
            if(!$currentUser->getIsEmployed()){
                throw new \Exception('you are no longer an employee');
            }
        
            $user= new User();
            $rangeSSID=range(0,12);
            shuffle($rangeSSID);
            $ssID=implode($rangeSSID);
            $user->setSsId($ssID);
            $form=$this->createForm(UserType::class,$user);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){ 
                
              /*  
              $file=$form['avatar']->getData(); 
              $fileName = md5(uniqid()).'.'.$file->guessExtension();
               // Move the file to the directory where brochures are stored
              $file->move(
                $this->getParameter('brochures_directory'),
               $fileName
         );
         
         // Update the 'brochure' property to store the PDF file name
         // instead of its contents
           $user->setAvatar($fileName); 
           */ 
            $plainPassword=$user->getPassword();
            $hashedPassword=$hasher->encodePassword($user,$plainPassword);
            $user->setPassword($hashedPassword);
            $user->setIsHashed(true);
            $userRepo=$em->getRepository(User::class);
            $pastUsers=$userRepo->findAll();

            
            foreach($pastUsers as $pastUser){
                if($pastUser->getEmail() == $user->getEmail()){
                    throw new \Exception('email already in use');
                }
            } 
                $user->setSlug($slugger->sluggify($user->getFirstName().' '.$user->getLastName()));
                $em->persist($user);
                //$this->assignContract($user);
                $em->flush();
                $this->addFlash('success','Collaborateur a été ajouté');
                return $this->redirectToRoute('rhpage',['slug'=>$currentUser->getSlug()]);
            } 
            
            
            return $this->render('rh/add_consultant.html.twig',['form'=>$form->createView()]);
         } 

         /**
          * @Route("/rh/{slug}/assignContracts",name="assignContracts")
          */ 

          public function assignContract($user) {
              $currentUser=$this->getUser();
              $em=$this->getDoctrine()->getManager();
              $pdfOptions=new Options();
              $pdfOptions->set('defaultFont','Arial');
              //$userRepo=$em->getRepository(User::class);
              //$users=$userRepo->findAll();
              $contractDirectory=$this->getParameter('contract_directory');
              $publicDirectory=$this->getParameter('doc_directory');
              
                  $domPdf=new Dompdf($pdfOptions);
                  
                  $htmlPdfFilePath=$contractDirectory.'/contract'.$user->getFirstName().'.html.twig';
                
                 $output='<div class="red"> Hello'.' '.$user->getFirstName().' ' .'here is a blank copy of your work contract </div>';
                
                  
                file_put_contents($htmlPdfFilePath,$output);
                  $domPdf->loadHtml($output);
                  $domPdf->setPaper('A4','portrait');
                  $domPdf->render();
                  $outputPDF=$domPdf->output();
                
                
                      $contractDocument=new MyDocument();
                      $contractDocument->setConsultant($user);
                      $contractDocument->setCategory('contract');
                      $contractDocument->setTitle('user contract');
                     
                      $pdfFilePath=$publicDirectory.'/'.$user->getFirstName().'contract.pdf';
                      file_put_contents($pdfFilePath,$outputPDF);

                      $start=strrpos($pdfFilePath,'/');
                      $end=strrpos($pdfFilePath,'f');
         
                      $urlToSet=substr($pdfFilePath,$start+1,($end-$start+1));
          
                      $contractDocument->setUrl($urlToSet); 
                      $em->persist($contractDocument);
                      
                
              $em->flush(); 

              return $this->redirectToRoute('userpage',['slug'=>$currentUser->getSlug()]);

              
          }

        
    }
?>