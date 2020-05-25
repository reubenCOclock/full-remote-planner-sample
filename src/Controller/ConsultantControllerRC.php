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
use App\Form\VacationType;
use App\Form\FormationType;
use App\Form\ProjectDaysType;
use App\Entity\MonthlySummary;
use App\Entity\PasswordUpdate;
use App\Form\MonthlySummaryType;
use App\Form\PasswordUpdateType;
use App\Form\ViewMonthlySummConsultantType;
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

class ConsultantControllerRC extends ConsultantUtilsController {  

        /**
         * Permet d'afficher le profil
         *
         * @Route("/user/{slug}/profile",name="account_profile_user")
         * 
         * @return Response
         */
        public function profile_card(User $user) {
            return $this->render('security/profile.html.twig', ['user'=>$user]);
        }
    
        /**
         * Permet d'afficher et de traiter le formulaire de modification du profil
         *
         * @Route("/user/{slug}/profile-update",name="account_profile_update_user")
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
                return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
            }

            return $this->render('security/profileupdate.html.twig', ['form'=>$form->createView()]);
        } 

       

      
        
        /**
         * Permet de changer le mot de passe
         * 
         *  @Route("/user/{slug}/password-update",name="account_password_user")
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
                    return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);

                }
            }

            return $this->render('security/password.html.twig', ['form' => $form->createView()]);

        }

        
     
        
        /**
         * Permet de déclarer un arrêt maladie
         * 
         * @Route("user/{slug}/SickDay",name="sickDay")
         * 
         */
        public function sickDay(Request $request, ObjectManager $manager,$slug, UserRepository $repo) {
            
            $sickDay= new SickDay(); 

            $now=new \DateTIme();
            
            $sickDay->setConsultant($this->getUser());

            $form = $this->createForm(SickDayType::class, $sickDay);
                        
            $form->handleRequest($request);

            $user=$this->getUser();

            $em=$this->getDoctrine()->getManager();

            if($form->isSubmitted() && $form->isValid()) { 
                $errors=false; 
                
                $amDocument=new MyDocument();

                $amDocument->setConsultant($user);

                $amDocument->setCategory('attestation arret maladie');
                // recuperation de l'attestaion d'arret maladie
                $pdfContent=$form['document']->getData();
                if($pdfContent == null){
                    $errors=true;
                    $this->addFlash('warning','viellez bien charger votre document d\'arret maladie');
                }

                // je deplace le fichier dans le projet
                else{
            
                $fileName = md5(uniqid()).'.'.$pdfContent->guessExtension();
                // Move the file to the directory where brochures are stored
               $pdfContent->move(
               $this->getParameter('doc_directory'),
               $fileName);
              
    
               $amDocument->setUrl($this->getParameter('doc_directory').'/'.$fileName);
    
               $em->persist($amDocument);


               $sickDay->setDocument($amDocument);
               }

               
                $startDate=$sickDay->getStartDate();
                $endDate=$sickDay->getEndDate();
               // verification des dates
                if($startDate->getTimeStamp() < $now->getTimeStamp()){
                    $errors=true;
                    $this->addFlash('warning','demande d\'arret maladie anterieur a aujourdhui');
                }
                if($sickDay->getStartDate()->getTimeStamp()> $sickDay->getEndDate()->getTimeStamp()){
                    $errors=true;
                    $this->addFlash('warning','la date de debut ne peux pas etre apres la date de fin');
                } 

                // je recupere les arrets maladies precedante correspondant au consultant
                
                // si les dates de cette arret maladie rentre en conflit avec les dates precedantes
             

                // verification que la durée de l'arret maladie ne depasse pas 30 jours
               $differenceInSeconds=$endDate->getTimeStamp() - $startDate->getTimeStamp();
            
               $differenceInDays=$differenceInSeconds/86400;
                if($differenceInDays > 30){
                    $errors=true;
                    $this->addFlash('warning','ta demande d\'arret maladie depasse 30 jours, elle peux pas etre automatisé, vieullez contacter ton admin pour en savoir plus ');
                } 
                // teste pour voir si l'arret maladie rentre en conflit avec une autre entité
                $testConflictWithOtherFormations=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDate->getTimeStamp(), $endDate->getTimeStamp(), Formation::class);
                if($testConflictWithOtherFormations == true){
                    $errors=true;
                    $this->addFlash('warning','tu a deja deposé une demande de formation pendent ces dates');
                }

                $testConflictWithOtherConges=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDate->getTimeStamp(),$endDate->getTimeStamp(),Vacation::class);
                if($testConflictWithOtherConges==true){
                    $errors=true;
                    $this->addFlash('warning','tu a deja deposé une demande de congé pendent ces dates');
                } 

                $testConflictWithOtherSubVacations=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDate->getTimeStamp(),$endDate->getTimeStamp(),SubVacation::class);
                if($testConflictWithOtherSubVacations == true){
                    $errors=true;
                    $this->addFlash('warning','tu a deja deposé une demande de sous congé pendent ces dates');
                }   

                $testConflictWithOtherSickDays=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDate->getTimeStamp(),$endDate->getTimeStamp(),SickDay::class);
                if($testConflictWithOtherSickDays == true){
                    $errors=true;
                    $this->addFlash('warning','tu a deja deposé une autre deamdne d\'arret maladie  pendant ces dates');
                }  

                // test pour voir si l'arret maladie n'englobe pas une autre demande

                $testEnglobedVacationRequest=$this->testEnglobingAbsenceRequest($startDate->getTimeStamp(),$endDate->getTimeStamp(),Vacation::class);

                if($testEnglobedVacationRequest == true){
                    $errors=true;
                    $this->addFlash('warning','ton arret maladie englobe une de tes demandes de congé');
                } 

                $testEnglobedFormationRequest=$this->testEnglobingAbsenceRequest($startDate->getTimeStamp(),$endDate->getTimeStamp(),Formation::class);

                if($testEnglobedFormationRequest==true){
                    $errors=true;
                    $this->addFlash('warning','ton arret maladie englobe une de tes demandes de congé');
                }

                $testSickDayEnglobingSickDay=$this->testEnglobingAbsenceRequest($startDate->getTimeStamp(),$endDate->getTimeStamp(),SickDay::class);
                if($testSickDayEnglobingSickDay==true){
                    $errors=true;
                    $this->addFlash('warning','ton arret maladie englobe une autre demande d\'arret maladie');
                }

                $testSickDayEnglobingSV=$this->testEnglobingAbsenceRequest($startDate->getTimeStamp(),$endDate->getTimeStamp(),SubVacation::class);
                if($testSickDayEnglobingSV==true){
                    $errors=true;
                    $this->addFlash('warning','ton arret maladie englobe une autre demande de sous congé');
                }
                


              

                
                if($errors==false){ 
                $this->calculateAbsentWorkingDays(SickDay::class,$user->getId());
                $manager ->persist($sickDay);        
                $manager ->flush();
                $this->addFlash('success','ton arret maladie a bien ete prise en compte');
              
                return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
                
                }
                
               

        }

        return $this->render('consultant/sick_day.html.twig', [
            'form'=>$form->createView()]);

    }

        /**
         * @Route("user/{slug}/demandeConge",name="demandeConge")
         */
        public function demandeDeConges(Request $request,$slug) { 
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            if($user->getIsEmployed()==false){
                throw new \Exception('you are no longer an employee');
            }
           
            $vacation=new Vacation();
            $form=$this->createForm(VacationType::class,$vacation);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                
                $errors = false;
                
                $startDay=$vacation->getStartDate();
                $endDay=$vacation->getEndDate(); 
                $startDayToInt=$startDay->getTimeStamp();
                $endDayToInt=$endDay->getTimeStamp();
                $now =new \DateTIme();
                $nowToInt=$now->getTimeStamp();
                

                // verification basique de conflits de dates
                
                if($startDayToInt <= $nowToInt){
                   $errors=true;
                   $this->addFlash ('warning',"Ta demande de congé est antérieure à aujourdhui, nous n'avons pas encore inventé le voyage en temps"); 
                   
                  }
                  

                if($startDayToInt > $endDayToInt){
                    $errors=true;
                    $this->addFlash('warning','vous ne pouvez pas commencé une congé apres que elle se termine');
                    
                } 

                // test pour voir si la demande de congé rentre en confit avec d'autres demandes d'absneces deja persisté

                $testConflictWithOtherFormations=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDayToInt, $endDayToInt,Formation::class);
               
                if($testConflictWithOtherFormations == true){
                    $errors=true;
                    $this->addFlash('warning','ta demande de congé rentre en conflit avec une de tes demandes de formation');
                } 

                $testConflictWithOtherVacations=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDayToInt, $endDayToInt,Vacation::class);
               
                if($testConflictWithOtherVacations == true){
                    $errors=true;
                    $this->addFlash('warning','ta demande de congé rentre en conflit avec une autre demande de congé');
                }

                

                $testConflictWithOtherSickDays=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDayToInt,$endDayToInt,SickDay::class);
              
                if($testConflictWithOtherSickDays==true){
                    $errors=true;
                    $this->addFlash('warning','ta demande de congé rentre en conflit avec une de tes arrets maladies');
                } 


                $testConflictWithOtherSV=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDayToInt,$endDayToInt,SubVacation::class);
              
                if($testConflictWithOtherSV==true){
                    $errors=true;
                    $this->addFlash('warning','ta demande de congé rentre en conflit avec une de tes sous-congés');
                } 



                 // test de voir si la demande de congé englobe une autre formation,arret maladie etc.

                $englobedFormationByVacation=$this->testEnglobingAbsenceRequest($startDayToInt,$endDayToInt,Formation::class); 


                $englobedSickDayByVacation=$this->testEnglobingAbsenceRequest($startDayToInt,$endDayToInt,SickDay::class);
     
     
                $vacationEnglobingVacation=$this->testEnglobingAbsenceRequest($startDayToInt,$endDayToInt,Vacation::class);
     
                $vacationEnglobingSubVacation=$this->testEnglobingAbsenceRequest($startDayToInt,$endDayToInt,SubVacation::class);
     
                
     
                
     
                if($englobedFormationByVacation == true){
                    $errors=true;
                    $this->addFlash('warning','ta demande de congé englobe une de vos demandes de formation');
                }
     
     
                if($englobedSickDayByVacation == true){
                 $errors=true;
                 $this->addFlash('warning','votre demande de congé englobe une de vos demandes de formation');
                }
     
                if($vacationEnglobingVacation == true ){
                    $errors=true;
                    $this->addFlash('warning','ta demande de congé englobe une autre demande de congé ou sous congé');
                } 
     
                if($vacationEnglobingSubVacation == true){
                    $errors=true;
                    $this->addFlash('warning','ta demande de sous congé englobe une autre demande de sous congé');
                }



                $subVacations=$vacation->getSubVacations()->toArray();
                
                if(!empty($subVacations)){
                foreach($subVacations as $value){ 
                    $value->setConsultant($user);
                    // si le type de vaccance de la sous congé correspond au type de vaccance de la congé
                    if($value->getTypeOfVacation()==$vacation->getTypeOfVacation()){
                        $errors=true;
                        $this->addFlash('warning','vous avez deja demandé ce type de congé dans votre saisi precedante');
                       
                    } 
                    $startDateSubVacation=$value->getStartDate();
                    $endDateSubVacation=$value->getEndDate();
                    $startDateSubVacationToINT=$startDateSubVacation->getTimeStamp();
                    $endDateSubVacationToINT=$endDateSubVacation->getTimeStamp();
                    // verification que la sous congé ne depasse pas 30 jours
                    $daysBetweenSubVacation=($endDateSubVacationToINT-$startDateSubVacationToINT)/86400;

                    
                

                    if($daysBetweenSubVacation > 30){
                        $errors=true;
                        $this->addFlash('warning','une sous congé ne peux pas depasser 30 jours');
                    } 
                    
                   
                    if($startDateSubVacationToINT>=$startDayToInt && $startDateSubVacationToINT <= $endDayToInt){ 
                        $errors=true;
                        $this->addFlash('warning','vos dates pour vos deux types de congé se chevauchent');
                       
                    } 
                     
                    if($endDateSubVacationToINT >= $startDayToInt && $endDateSubVacationToINT <=$endDayToInt) {
                        $errors=true;
                        $this->addFlash('warning','vos dates pour vos deux types de congé se chevauchent');
                    }
               
                    if($startDateSubVacationToINT < $startDayToInt){
                        $erros=true;
                        $this->addFlash('warning','vous ne pouvez pas posé une demande de sous congé avant votre demande de congé');
                        
                    } 
                
                    if($startDateSubVacationToINT > $endDateSubVacationToINT){
                        $errors=true;
                        $this->addFlash('warning','vous ne pouves pas commencé une congé apres que elle se termine');
                      
                    } 

                    // meme logique pour les sous congés
               
                    
                    $testConflictsWithOtherFormation=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDateSubVacationToINT, $endDateSubVacationToINT,Formation::class);
                   
                    if($testConflictsWithOtherFormation==true){
                        $errors=true;
                        $this->addFlash('warning','ta demande de sous congé rentre en conflit avec une de tes demandes de formation');
                    } 
                    
                   
                    $testConflictWithOtherSickDay=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDateSubVacationToINT, $endDateSubVacationToINT,SickDay::class);

                    
                    if($testConflictWithOtherSickDay){
                        $errors=true;
                       
                        $this->addFlash('warning','ta demande de sous congé rentre en conflit avec une de tes arrets maladies');
                    } 
                
                    $testConflictWithOtherVacations=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDateSubVacationToINT, $endDateSubVacationToINT,Vacation::class);

                    
                    if($testConflictWithOtherVacations){
                        $errors=true;
                       
                        $this->addFlash('warning','ta demande de sous congé rentre en conflit avec une autre demande de congé');
                    } 

                    
                    $testConflictWithOtherSV=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDateSubVacationToINT, $endDateSubVacationToINT,SubVacation::class);

                    
                    if($testConflictWithOtherSV){
                        $errors=true;
                       
                        $this->addFlash('warning','ta demande de sous congé rentre en conflit avec une autre demande de sous congé');
                    } 

                    
                   

                    $englobedSickDayBySubVacation= $this->testEnglobingAbsenceRequest($startDateSubVacationToINT,$endDateSubVacationToINT,SickDay::class);

                    if($englobedSickDayBySubVacation == true){
                        $errors=true;

                        $this->addFlash('warning','ta demande de sous-congé englobe un de tes arrets maladies');
                    }

                    $englobedFormationBySubVacation=$this->testEnglobingAbsenceRequest($startDateSubVacationToINT,$endDateSubVacationToINT,Formation::class);
                    if($englobedFormationBySubVacation == true){ 
                        
                        $errors=true;
                        $this->addFlash('warning','ta demande de sous-congé englobe une de tes demandes de formation ');
                    }

                    $subVacationEnglobingVacation=$this->testEnglobingAbsenceRequest($startDateSubVacationToINT,$endDateSubVacationToINT,Vacation::class);

                    if($subVacationEnglobingVacation==true){
                        $errors=true;
                        $this->addFlash('warning','ta demande de sous congé englobe une autre demande de congé');
                    }

                    $subVacationEnglobingSubVacation=$this->testEnglobingAbsenceRequest($startDateSubVacationToINT,$endDateSubVacationToINT,SubVacation::class);

                    if($subVacationEnglobingSubVacation==true){
                        $errors=true;
                        $this->addFlash('warning','ta demande de sous congé englobe une autre demande de sous congé');
                    }

                    if($errors==false){
                    $em->persist($value);
                    }

                }
            }
                
            
                
                if($errors==false){      
                $vacation->setConsultant($user);
                $this->calculateAbsentWorkingDays(Vacation::class,null,$vacation);
                $this->addFlash('success',"merci votre demande de congé a bien ete enregistré");
                $em->persist($vacation);
                $em->flush();
                return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
              } 
            }
            return $this->render('consultant/demande_conge_form.html.twig',['form'=>$form->createView()]);
        } 
        
       
        

        /**
         * @Route("user/{slug}/MesConges/{id}", name="viewConges")
         */
        public function viewDemandeConges($id){
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            if($user->getIsEmployed()==false){
                throw new \Exception('you are no longer an employee here');
            }
            
            $vacationRepo=$em->getRepository(Vacation::class);
            $vacations=$vacationRepo->sortAbsencesByUserAndEndDate($user->getId());


            // appel a la fonction pour calculer le nombre de jours ouvré dans toutes les congés correspondant au consultant
            $this->calculateAbsentWorkingDays(Vacation::class,$id); 

            $now=new \DateTIme();

           
            return $this->render('consultant/view_conge.html.twig',['vacations'=>$vacations,'now'=>$now]);
        } 

        /**
         * @Route("user/{slug}/modifyConge/{id}", name="modifyConge")
         */
        
        public function modifierDemandeConge($id, Request $request) {
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();

            $vacationRepo=$em->getRepository(Vacation::class);
            
            // recuperation de la congé ciblé
            $vacation=$vacationRepo->find($id);
            // recuperation de l'information via le formulaire
            $form=$this->createForm(VacationType::class,$vacation);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

                
                 $errors=false;

                 $now=new \DateTIme();
                 

                 $vacation->setUpdatedAt($now);

                 if($vacation->getStartDate()->getTimeStamp() < $now->getTimeStamp()){
                     $errors=true;
                     $this->addFlash('warning','votre congé commence avant aujourdhui');
                 }

                 if($vacation->getEndDate() < $vacation->getStartDate()){
                     $errors=true;
                     $this->addFlash('warning','votre congé commence avant qu\'elle termine');
                 } 

                
                 // appelle a la fonction pour tester des conflits en passant la congé visé en parametre pour ne la pas prendre en compte
                 $testConflictsWithOtherVacations=$this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$vacation->getStartDate()->getTimeStamp(),$vacation->getEndDate()->getTimeStamp(),Vacation::class,$vacation);

                 if($testConflictsWithOtherVacations == true ){
                     $errors=true;
                     $this->addFlash('warning','votre demande de congé rentre en conflit avec une autre demande de congé');
                 } 

                 $testConflictsWithOtherFormations=$this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$vacation->getStartDate()->getTimeStamp(),$vacation->getEndDate()->getTimeStamp(),Formation::class,$vacation);

                 if($testConflictsWithOtherFormations == true){
                    $errors=true;
                    $this->addFlash('warning','votre demande de congé rentre en conflit avec une autre demande de formation');
                 } 

                 $testConflictsWithOtherSickDays=$this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$vacation->getStartDate()->getTimeStamp(),$vacation->getEndDate()->getTimeStamp(),SickDay::class,$vacation);

                 if($testConflictsWithOtherSickDays == true ){
                    $errors=true;
                    $this->addFlash('warning','votre demande de congé rentre en conflit avec une autre demande de arret maladie');
                 }  

                 // tester si la modification englobe une autre valeur

                 $testConflictsWithOtherSV=$this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$vacation->getStartDate()->getTimeStamp(),$vacation->getEndDate()->getTimeStamp(),SubVacation::class,$vacation);

                 if($testConflictsWithOtherSV == true ){
                    $errors=true;
                    $this->addFlash('warning','votre demande de congé rentre en conflit avec une autre demande de sous congé');
                 } 

                 $englobedVacations=$this->testEnglobingAbsenceRequest($vacation->getStartDate()->getTimeStamp(),$vacation->getEndDate()->getTimeStamp(),Vacation::class);

                 if($englobedVacations){
                    $errors=true;
                    $this->addFlash('warning','votre demande de congé englobe une autre demande de congé');
                 }

                 $englobedFormations=$this->testEnglobingAbsenceRequest($vacation->getStartDate()->getTimeStamp(),$vacation->getEndDate()->getTimeStamp(),Formation::class);

                 if($englobedFormations){
                    $errors=true;
                    $this->addFlash('warning','votre demande de congé englobe une  demande de formation');
                 }

                 $englobedSickDays=$this->testEnglobingAbsenceRequest($vacation->getStartDate()->getTimeStamp(),$vacation->getEndDate()->getTimeStamp(),SickDay::class);


                 if($englobedSickDays){
                    $errors=true;
                    $this->addFlash('warning','votre demande de congé englobe une arret maladie');
                 }
                 // meme traitement avec les sous congé
                 
                 $subVacations=$vacation->getSubVacations()->toArray();

                 foreach($subVacations as $subVacation){
                     if($subVacation->getStartDate()->getTimeStamp() >= $vacation->getStartDate()->getTimeStamp() && $subVacation->getStartDate()->getTimeStamp() <= $vacation->getEndDate()->getTimeStamp()){
                         $errors=true;
                         $this->addFlash('warning','votre demande de sous congé rentre en conflit avec votre demande de congé');
                     } 

                     if($subVacation->getEndDate()->getTimeStamp() >= $vacation->getStartDate()->getTimeStamp() && $subVacation->getEndDate()->getTimeStamp() <= $vacation->getEndDate()->getTimeStamp()){
                        $errors=true;
                        $this->addFlash('warning','votre demande de sous congé rentre en conflit avec votre demande de congé');
                    } 
                    
                    if($subVacation->getStartDate()->getTimeStamp() < $vacation->getStartDate()->getTimeStamp()){
                        $errors=true;
                        $this->addFlash('warning','impossible de commencer une sous congé avant une congé');
                    } 

                    $testSVConflictsWithOtherVacations=$this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$subVacation->getStartDate()->getTimeStamp(),$subVacation->getEndDate()->getTimeStamp(),Vacation::class,$subVacation);

                    if($testSVConflictsWithOtherVacations == true ){
                       $errors=true;
                       $this->addFlash('warning','votre demande de sous congé rentre en conflit avec une  demande de congé');
                    } 


                    $testSVConflictsWithOtherFormations=$this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$subVacation->getStartDate()->getTimeStamp(),$subVacation->getEndDate()->getTimeStamp(),Formation::class,$subVacation);

                    if($testSVConflictsWithOtherFormations == true ){
                       $errors=true;
                       $this->addFlash('warning','votre demande de sous congé rentre en conflit avec une  demande de formation');
                    } 


                    $testSVConflictsWithOtherSickDays=$this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$subVacation->getStartDate()->getTimeStamp(),$subVacation->getEndDate()->getTimeStamp(),SickDay::class,$subVacation);

                    if($testSVConflictsWithOtherSickDays == true ){
                       $errors=true;
                       $this->addFlash('warning','votre demande de sous congé rentre en conflit avec une  demande de maladie');
                    } 


                    $testSVConflictsWithOtherSV=$this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$subVacation->getStartDate()->getTimeStamp(),$subVacation->getEndDate()->getTimeStamp(),SubVacation::class,$subVacation);

                    if($testSVConflictsWithOtherSickDays == true ){
                       $errors=true;
                       $this->addFlash('warning','votre demande de sous congé rentre en conflit avec une  demande de sous congé');
                    }  

                    $englobedSVSickDays=$this->testEnglobingAbsenceRequest($subVacation->getStartDate()->getTimeStamp(),$subVacation->getEndDate()->getTimeStamp(),SickDay::class);


                    if($englobedSVSickDays){
                       $errors=true;
                       $this->addFlash('warning','votre demande de sous congé englobe une arret maladie');
                    }  

                    $englobedSVFormations=$this->testEnglobingAbsenceRequest($subVacation->getStartDate()->getTimeStamp(),$subVacation->getEndDate()->getTimeStamp(),Formation::class);

                    if($englobedSVFormations){
                        $errors=true;
                        $this->addFlash('warning','votre demande de sous congé englobe une demande de formation');
                     }  

                     $englobedSVVacations=$this->testEnglobingAbsenceRequest($subVacation->getStartDate()->getTimeStamp(),$subVacation->getEndDate()->getTimeStamp(),Vacation::class);

                     if($englobedSVVacations){
                        $errors=true;
                        $this->addFlash('warning','votre demande de sous congé englobe une demande de congé');
                     }  

                     $englobedSVSubVacations=$this->testEnglobingAbsenceRequest($subVacation->getStartDate()->getTimeStamp(),$subVacation->getEndDate()->getTimeStamp(),SubVacation::class);

                     if($englobedSVSubVacations){
                         $errors=true;
                         $this->addFlash('warning','votre demande de sous congé englobe une demande de congé');
                         
                     } 

                    
                     
                  


            } 

            // si tout va bien persist et insertion
            if($errors == false){
                $vacation->setIsValidated(false);    
                $em->persist($vacation);
             
                $em->flush();
                $this->addFlash('success','ta demande de congé a bien été modifié');
                return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
           } 
         

            
        }
        return $this->render('consultant/modify_conge.html.twig',['form'=>$form->createView()]);

    } 

    /**
     * @Route("user/{slug}/deleteDemandeConge/{id}", name="deleteDemandeConge")
     */

    public function deleteDemandeConge($id){

        $user=$this->getUser();

        $em=$this->getDoctrine()->getManager();

        $vacationRepo=$em->getRepository(Vacation::class);

        $vacation=$vacationRepo->find($id);

        $subVacations=$vacation->getSubVacations()->toArray();
        if(!empty($subVacations)){
        foreach($subVacations as $subVacation){
            $em->remove($subVacation);
        }
     }
        //$em->persist($vacation);
        $em->remove($vacation);

        $em->flush();

        $this->addFlash('success','votre demande de congé a bien été supprimé');

        return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
    }
    

        /**
         * @Route("user/{slug}/DemandeDeFormation", name="demandeFormation")
         */

        public function demandeFormation(Request $request) {
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            if($user->getIsEmployed()==false){
                throw new \Exception('you are no longer an employee');
            }
            $formation=new Formation();
            $form=$this->createForm(FormationType::class, $formation);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()) { 
                
                $errors = false;
               
                $startDay=$formation->getStartDate();
                $endDay=$formation->getEndDate();
                
                $startDayToInt=$startDay->getTimeStamp();
                $endDayToInt=$endDay->getTimeStamp();
                
                $now =new \DateTIme();
                $nowToInt=$now->getTimeStamp();
                
                if($endDayToInt < $startDayToInt){
                    throw new \Exception('unable to end before you start');
                } 

                
    
                if($startDayToInt <= $nowToInt){
                    $errors=true;
                 $this->addFlash ('warning',"Ta demande de congé est antérieure à aujourdhui, nous n'avons pas encore inventé le voyage en temps"); 
                 return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
            
                }  
                
                $formation->setConsultant($user);
               
                $formationRepo=$em->getRepository(Formation::class);
                $pastFormations=$formationRepo->findBy(['consultant'=>$user->getId()]); 
            
         

            $difference=$endDayToInt-$startDayToInt;
           
            if($difference/86400 > 30){
                $errors=true;
                $this->addFlash('warning','Tu demande une formation qui depasse les 30 jours, cette demande ne peut pas etre automatisé, viuellez contacter ton admin pour en savoir plus');
            } 

            $checkConflictsWithOtherVac=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDayToInt,$endDayToInt,Vacation::class);
            if($checkConflictsWithOtherVac==true){
                $errors=true;
                $this->addFlash('warning','ta demande de formation rentre en conflit avec une de tes demandes de congé');
            } 

            $checkConflictWithOtherSV=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDayToInt,$endDayToInt,SubVacation::class);
            if($checkConflictWithOtherSV==true){
                $errors=true;
                $this->addFlash('warning','ta demande de formation rentre en conflit avec une de tes demandes de sous congé');
            }

            $checkConflictsWithOtherSickDays=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDayToInt, $endDayToInt,SickDay::class);
           
            if($checkConflictsWithOtherSickDays==true){
                $errors=true;
                $this->addFlash('warning','ta demande de formation rentre en conflit avec une de tes demandes de congé');
            }

            $checkConflictsWithOtherFormations=$this->testConflictsBetweenTypesOfAbsence($user->getId(),$startDayToInt, $endDayToInt,Formation::class);
           
            if($checkConflictsWithOtherFormations==true){
                $errors=true;
                $this->addFlash('warning','ta demande de formation rentre en conflit avec une autre demande de formation');
            }

            $checkEnglobedVacations=$this->testEnglobingAbsenceRequest($startDayToInt,$endDayToInt,Vacation::class);
            if($checkEnglobedVacations == true){ 
                $errors=true;
                $this->addFlash('warning','votre demande de formation englobe une demande de congé');
            } 

            $checkEnglobedSubVacations=$this->testEnglobingAbsenceRequest($startDayToInt,$endDayToInt,SubVacation::class);

            if($checkEnglobedSubVacations==true){
                $errors=true;
                $this->addFlash('warning','ta demande de formation englobe une demande de sous congé');
            } 
            
            $checkEnglobedSickDays=$this->testEnglobingAbsenceRequest($startDayToInt,$endDayToInt,SickDay::class);

            if($checkEnglobedSickDays==true){
                $errors=true;
                $this->addFlash('warning','ta demande de formation englobe une demande de sous congé');
            }

            $checkEnglobedFormations=$this->testEnglobingFormations=$this->testEnglobingAbsenceRequest($startDayToInt,$endDayToInt,Formation::class);

            if($checkEnglobedFormations==true){
                $errors=true;
                $this->addFlash('warning','ta demande de formation englobe une autre demande de formation');
            }
            
            if($errors==false){
            $em->persist($formation);
            $em->flush();  

            $this->calculateAbsentWorkingDays(Formation::class,null,$formation);

            $this->addFlash('success',"merci votre demande de formation a bien ete prise en compte");
            return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
            }
        }

        return $this->render('consultant/demande_formation.html.twig',['form'=>$form->createView()]);
    } 
    /**
     * @Route("user/{slug}/MesDemandesDeFormation/{id}",name="viewDemandeFormation")
     */

    public function viewDemandeFormations($id){
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();
        if($user->getIsEmployed()==false){
            throw new \Exception('you are no longer an employee here');
        }

        $now =new \DateTIme();
        $formationRepo=$em->getRepository(Formation::class);
        $formations=$formationRepo->sortAbsencesByUserAndEndDate($user->getId());
        $this->calculateAbsentWorkingDays(Formation::class,$id,null);

        
        
        return $this->render('consultant/view_formation.html.twig',['formations'=>$formations,'now'=>$now]);

    } 
    
    /**
     * @Route("user/{slug}/modifierDemandeFormation/{id}", name="modifierDemandeFormation")
     */
    
    public function modifierDemandeDeFormation($id, Request $request){
        $em=$this->getDoctrine()->getManager();

        $formationRepo=$em->getRepository(Formation::class);

        $formation=$formationRepo->find($id);

        $user=$this->getUser();
        
        

        $form=$this->createForm(FormationType::class,$formation);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           $errors=false;

           if($this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$formation->getStartDate()->getTimeStamp(),$formation->getEndDate()->getTimeStamp(),Formation::class,$formation)){
               $errors=true;
               $this->addFlash('warning','ta demande de formation rentre en conflit avec une autre demande de formation');
           }

           if($this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$formation->getStartDate()->getTimeStamp(),$formation->getEndDate()->getTimeStamp(),Vacation::class,$formation)){
            $errors=true;
            $this->addFlash('warning','ta demande de formation rentre en conflit avec une autre demande de congé');
          }  

          if($this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$formation->getStartDate()->getTimeStamp(),$formation->getEndDate()->getTimeStamp(),SubVacation::class,$formation)){
            $errors=true;
            $this->addFlash('warning','ta demande de formation rentre en conflit avec une autre demande de sous congé');
          } 
          
          if($this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$formation->getStartDate()->getTimeStamp(),$formation->getEndDate()->getTimeStamp(),SickDay::class,$formation)){
            $errors=true;
            $this->addFlash('warning','ta demande de formation rentre en conflit avec une autre demande de arret maladie');
          }  


          if($this->testEnglobingAbsenceRequest($formation->getStartDate()->getTimeStamp(),$formation->getEndDate()->getTimeStamp(),Formation::class)){
              $errors=true;
              $this->addFlash('warning','ta demande de formation englobe une autre demande de formation');
          }

          if($this->testEnglobingAbsenceRequest($formation->getStartDate()->getTimeStamp(),$formation->getEndDate()->getTimeStamp(),Vacation::class)){
            $errors=true;
            $this->addFlash('warning','ta demande de formation englobe une autre demande de Congé');
        }

        if($this->testEnglobingAbsenceRequest($formation->getStartDate()->getTimeStamp(),$formation->getEndDate()->getTimeStamp(),SickDay::class)){
            $errors=true;
            $this->addFlash('warning','ta demande de formation englobe une demande d\'arret maladie');
        }

        if($this->testEnglobingAbsenceRequest($formation->getStartDate()->getTimeStamp(),$formation->getEndDate()->getTimeStamp(),SubVacation::class)){
            $errors=true;
            $this->addFlash('warning','ta demande de formation englobe une demande de sous congé');
        } 

        if($errors == false){
            $formation->setIsValidated(false);
            $em->persist($formation);
            $em->flush();
           
            $this->addFlash('success','demande de formation bien modifié');
            return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
        }


        }

      
        return $this->render('consultant/modify_formation.html.twig',['form'=>$form->createView()]);
    } 

    /**
     * @Route("user/{slug}/deleteFormation/{id}", name="deleteFormation")
     */


    public function deleteDemandeFormation($id){

        $user=$this->getUser();

        $em=$this->getDoctrine()->getManager();

        $formationRepo=$em->getRepository(Formation::class);

        $formation=$formationRepo->find($id);

        $em->remove($formation);

        $em->flush();

        $this->addFlash('success','demande de formation supprimer');

        return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
    } 

    /**
     * @Route("user/{slug}/viewMonthlySumms", name="viewMonthlySumms")
     */
    public function viewMonthlyRecapsByMonth(Request $request){
        $user=$this->getUser();

        $form=$this->createForm(ViewMonthlySummConsultantType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();

            $monthlySummRepo=$em->getRepository(MonthlySummary::class);
            
            $selectedMonth=intval($form->get('month')->getViewData());
            
            $userMonthlySummaries=$monthlySummRepo->findBy(['month'=>$selectedMonth,'consultant'=>$user->getId()]);

            $actualMonth=$this->getActualMonthFromInt($selectedMonth);
            
            return $this->render('consultant/view_monthly_summs.html.twig',['userMonthlySummaries'=>$userMonthlySummaries,'actualMonth'=>$actualMonth]);
            
        }

        return $this->render('consultant/choose_monthly_summs.html.twig',['form'=>$form->createView()]);
    }

    /**
     * @Route("user/{slug}/viewArretsMaladies", name="viewArretsMaladies")
     */

    public function viewArretsMaladie(){
        $user=$this->getUser(); 

        $now=new \DateTIme();

        $em=$this->getDoctrine()->getManager();

        $sickDayRepo=$em->getRepository(SickDay::class);

        $sickDays=$sickDayRepo->findBy(['consultant'=>$user->getId()]); 

        $this->calculateAbsentWorkingDays(SickDay::class,$user->getId());

        return $this->render('consultant/view_sick_days.html.twig',['sickDays'=>$sickDays,'now'=>$now]);
    } 

    /**
     * @Route("user/{slug}/modifierAM/{id}", name="modifierAM")
     */
    
    public function modifyArretMaladie($id,Request $request){
        $user=$this->getUser();

        $em=$this->getDoctrine()->getManager();

        $arretMaladieRepo=$em->getRepository(SickDay::class);

        $amToModify=$arretMaladieRepo->find($id);

        $form=$this->createForm(SickDayType::class,$amToModify);

        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid()){
            $errors=false;  

            $now=new \DateTIme();

            $nowTS=$now->getTimeStamp();

            if($amToModify->getStartDate()->getTimeStamp() < $nowTS){
                $errors=true;
                $this->addFlash('warning','impossible de commencer une congé avent aujourdhui');
            } 

            if($amToModify->getStartDate()->getTimeStamp() > $amToModify->getStartDate()->getTimeStamp()){
                $errors=true;
                $this->addFlash('warning','impossible de commencer apres que l\'on termine');
            } 

            
            
            if($this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$amToModify->getStartDate()->getTimeStamp(),$amToModify->getEndDate()->getTimeStamp(),SickDay::class,$amToModify)){
                $errors=true;
                $this->addFlash('warning','votre modification d\'arret maladie rentre en conflit avec une autre demande d\'arret maladie');
            } 

            if($this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$amToModify->getStartDate()->getTimeStamp(),$amToModify->getEndDate()->getTimeStamp(),Formation::class,$amToModify)){
                $errors=true;
                $this->addFlash('warning','votre modification d\'arret maladie rentre en conflit avec une de vos demandes de formation');
            } 

            if($this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$amToModify->getStartDate()->getTimeStamp(),$amToModify->getEndDate()->getTimeStamp(),Vacation::class,$amToModify)){
                $errors=true;
                $this->addFlash('warning','votre modification d\'arret maladie rentre en conflit avec une de vos demandes de congés');
            } 
            
            
            if($this->testConflictsBetweenTypesOfAbsenceModifyAbsence($user->getId(),$amToModify->getStartDate()->getTimeStamp(),$amToModify->getEndDate()->getTimeStamp(),SubVacation::class,$amToModify)){
                $errors=true;
                $this->addFlash('warning','votre modification d\'arret maladie rentre en conflit avec une de vos demandes de sous congés');
            }  

            if($this->testEnglobingAbsenceRequest($amToModify->getStartDate()->getTimeStamp(),$amToModify->getEndDate()->getTimeStamp(),SickDay::class)){
                $errors=true;
                $this->addFlash('warning','votre modification d\'arret maladie englobe une autre demande d\'arret maladie');
            } 

            if($this->testEnglobingAbsenceRequest($amToModify->getStartDate()->getTimeStamp(),$amToModify->getEndDate()->getTimeStamp(),Formation::class)){
                $errors=true;
                $this->addFlash('warning','votre modification d\'arret maladie englobe une de vos demandes de formation');
            } 

            if($this->testEnglobingAbsenceRequest($amToModify->getStartDate()->getTimeStamp(),$amToModify->getEndDate()->getTimeStamp(),Vacation::class)){
                $errors=true;
                $this->addFlash('warning','votre modification d\'arret maladie englobe une de vos demandes de congé');
            }
            
            if($this->testEnglobingAbsenceRequest($amToModify->getStartDate()->getTimeStamp(),$amToModify->getEndDate()->getTimeStamp(),SubVacation::class)){
                $errors=true;
                $this->addFlash('warning','votre modification d\'arret maladie englobe une de vos demandes de sous congé');
            } 

            if($errors==false){
                $em->persist($amToModify);
                $em->flush();
                $this->addFlash('success','arret maladie modifié');
                return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
            }




        }

        return $this->render('consultant/modify_am.html.twig',['form'=>$form->createView()]);


    } 

    /**
     * @Route("user/{slug}/deleteDemandeAM/{id}", name="deleteDemandeAM")
     */

    public function deleteDemandeAM($id){

        $user=$this->getUser();

        $em=$this->getDoctrine()->getManager();

        $amRepo=$em->getRepository(SickDay::class);

        $amToModify=$amRepo->find($id);

        $em->persist($amToModify);

        $em->remove($amToModify);

        

        $em->flush();

        $this->addFlash('success','arret maladie suprrimé');

        return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
    }

  


    
   

   
}
?>
    
    