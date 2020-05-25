<?php

namespace App\Controller;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\User;
use App\Entity\Role;
use App\Entity\PasswordUpdate;
use App\Entity\ConsultantProjectPricesRegie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request; 
use App\Entity\Vacation;
use App\Form\VacationType;
use App\Entity\Formation;
use App\Form\ProjectRegieType;
use App\Form\ProjectForfaitType;
use App\Form\ViewFacturesByMonthType;
use App\Form\FormationType;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Form\UserClientType;
use App\Form\UserRHType;
use App\Utils\Slugger;
use App\Entity\Feedback;
use App\Entity\SickDay;
use App\Form\AccountType;
use App\Entity\MonthlySummary;
use App\Form\FacturationFormType;
use App\Form\SickDayType;
use App\Entity\MyDocument;
use App\Entity\FilterConge;
use App\Form\ChooseAbsenceFilterType;
use App\Form\PasswordUpdateType;
use Doctrine\Common\Collections\Collection;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\ProjectForfaitLivrables;


    
    

class AdminControllerRC extends AdminUtilsController{ 

         /**
         * Permet d'afficher le profil
         *
         * @Route("/admin/{slug}/profile",name="account_profile_admin")
         * 
         * @return Response
         */
        public function profile_card(User $user) {
            return $this->render('security/profile.html.twig', ['user'=>$user]);
        } 
    
    
        /**
         * Permet d'afficher et de traiter le formulaire de modification du profil
         *
         * @Route("/admin/{slug}/profile-update",name="account_profile_update_admin")
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
            }

            return $this->render('security/profileupdate.html.twig', ['form'=>$form->createView()]);
        }

        /**
         * Permet de changer le mot de passe
         * 
         *  @Route("/admin/{slug}/password-update",name="account_password_admin")
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

                    return $this->redirectToRoute('adminpage',['slug'=>$user->getSlug()]);

                }
            }

            return $this->render('security/password.html.twig', ['form' => $form->createView()]);

        }

        /**
         * @Route("/admin/consultProjects/{slug}",name="consultProjects")
         */
        public function consultProject(){
            $em=$this->getDoctrine()->getManager();
            $projectRepo=$em->getRepository(Project::class);
            $projects=$projectRepo->findAll();
            $userRepo=$em->getRepository(User::class);
            $users=$userRepo->findAll();
           
            
            return $this->render('admin/view_modify_project.html.twig',['projects'=>$projects]);
        } 

        /**
         * @Route("/admin/{slug}/markProjectAsFinished/{id}",name ="markProjectAsFinished")
         */

        public function markProjectAsFinished($id){
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            $projectRepo=$em->getRepository(Project::class);
            $project=$projectRepo->find($id);
            $project->setIsActive(false);
            $this->addFlash('warning','Projet clôturé');
            $em->flush();
            return $this->redirectToRoute('adminpage',['slug'=>$user->getSlug()]);
        } 
        /**
         * @Route("/admin/addProject/{slug}",name="addProject")
         */
        public function ajouterUnProjet(Request $request){ 
            $session = new Session();
            $em=$this->getDoctrine()->getManager();
            $currentUser=$this->getUser();
            $project=new Project();
            $users=$em->getRepository(User::class);
            $currentUser=$this->getUser();
            $project->setManager($currentUser);

            // formulaire pour ajouter un projet
           
            $form=$this->createForm(ProjectType::class,$project);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                
               
                // le traitement selon les options, dans le as d'un projet fofait ou regie, une session est crée pour stocker le projet qui sera finalisé avec un sous formulaire en fonction du type de prjet choisi. Si le type de projet ajouté est un regis ou forfait on redirige a la route qui appelle la fonction executeContractChoice

                if($form->get('forfait')->isClicked()){
                    $project->setContractType('Forfait');
                    $session->set('project',$project);
                    return $this->redirectToRoute('executeContractChoice',['slug'=>$currentUser->getSlug(),'contractType'=>$project->getContractType()]);
                    
                   
                }

                if($form->get('regie')->isClicked()){
                    $project->setContractType('Regis');
                    $session->set('project',$project);
                    return $this->redirectToRoute('executeContractChoice',['slug'=>$currentUser->getSlug(),'contractType'=>$project->getContractType()]);
                    
                   
                }

                if($form->get('intercontrat')->isClicked()){
                    $project->setContractType('InterContrat');
                    $em->persist($project);
                    $em->flush();
                    $this->addFlash('success','ton projet intercontrat a bien été crée');
                    return $this->redirectToRoute('userpage',['slug'=>$currentUser->getSlug()]);
                    
                    
                }

                
                
             
            }

            return $this->render('admin/add_project.html.twig',['form'=>$form->createView()]);
            
        }

        /**
         * @Route("admin/{slug}/executeContractChoice/{contractType}", name="executeContractChoice")
         */
        
         // cette fonction sera appelé dans la fonction ajouterUnProjet
        public function executeContractChoice($contractType, Request $request){
            $session=new Session();

            $em=$this->getDoctrine()->getManager();
            $userRepo=$em->getRepository(User::class);

             $user=$this->getUser();


            if($contractType=='Regis'){

                // recuperation du projet alimenté dans la fonction ajouterUnProjet mais qui n'a pas été persisté!
                $currentProject=$session->get('project');
                // creation d'un nouveau projet
                $project=new Project();
                // application des valeurs stocké dans la session dans ce nouveau projet
                $project->setTitle($currentProject->getTitle());

                $project->setDescription($currentProject->getDescription());

                $project->setClient($currentProject->getClient());
                // recuperation du client associé a ce projet stocké dans la session currentProject,cette valuer retournera un seul client chaque fois sous forme de tableau
                $projectClient=$userRepo->associateClientToProject($currentProject->getClient()->getId());
                //attribution de ce client a ce projet
                $project->setClient($projectClient[0]); 

                $project->setContractType($currentProject->getContractType());

                $project->setManager($user);

              
                // recuperation des consultants attribué a ce projet
                $projectConsultants=$currentProject->getConsultants()->toArray();

                

                foreach($projectConsultants as $consultant){ 
                    //recuperation du consultant attribué a ce projet, cette valuer retournera un seul consultant chaque fois sous forme de tableau
                   $consultantToAdd=$userRepo->associateConsultantToProject($consultant->getId());
                  
                   //ajout de ce consultant au projet
                   $project->addConsultant($consultantToAdd[0]);

                    //creation de l'entité permettant un sous formualire pour attribuer un consultant a un prix
                   $projectConsultantPrice=new ConsultantProjectPricesRegie();

                   $projectConsultantPrice->setConsultant($consultantToAdd[0]);

                   $projectConsultantPrice->setConsultantFirstName($consultantToAdd[0]->getFirstName());

                   // creation du sous formualire lui meme
                    
                   $project->addConsultantProjectPricesRegy($projectConsultantPrice);

                  
                }

           
                // creation d'un formualaire uniquement pour permettre l'affichage du sous formualire permettant la possiilité d'attribuer un prix pour un consultant, ce formulaire est lié au projet en cours
                $form=$this->createForm(ProjectRegieType::class,$project);
                $form->handleRequest($request);


                // persist et flush du projet
                if($form->isSubmitted() && $form->isValid()){
                    $em->persist($project);
                    
                    $em->flush();
                    
                    $this->addFlash('success','merci,votre projet regis vient d\'etre énregistré');
                    return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
                }

                

               
                return $this->render('admin/execute_project_regis.html.twig',['project'=>$project,'form'=>$form->createView()]);
            }

            else if($contractType=='Forfait'){

                // recuperation du projet en question
                $currentProject=$session->get('project');
                // creation d'un nouveau projet
                $project=new Project();
                // attribution des valuers a ce nouveau projet
                $project->setTitle($currentProject->getTitle());

                $project->setDescription($currentProject->getDescription());

                $project->setClient($currentProject->getClient());

                $projectClient=$userRepo->associateClientToProject($currentProject->getClient()->getId());
                //dd($projectClient[0]);
                $project->setClient($projectClient[0]);

                $project->setContractType($currentProject->getContractType());

                $project->setManager($user);



                $projectConsultants=$currentProject->getConsultants()->toArray();


                foreach($projectConsultants as $consultant){ 
                    // recuperation et attribution des consultants a ce projet
                   $consultantToAdd=$userRepo->associateConsultantToProject($consultant->getId());
                  
                   $project->addConsultant($consultantToAdd[0]);


                }

              

                // creation d'un sous formualire pour ajouter des livrables a un projet forfait permettant d'ajouter des livrables a ce projet
                $form=$this->createForm(ProjectForfaitType::class,$project);

                $form->handleRequest($request);

                if($form->isSubmitted() && $form->isValid()){ 
                
                    $errors=false;
                    $now=new \DateTIme();
                    $nowTS=$now->getTimeStamp();

                   // verification pour voir que des livrables ont bien ete ajouter
                    if(empty($project->getProjectForfaitLivrables()->toArray())){
                       
                        $errors=true;
                        $this->addFlash('warning','vous avez besoin d\'ajouter des livrables pour un projet de type forfait');
                    }

                    else{
                        $livrables=$project->getProjectForfaitLivrables()->toArray();

                        // verification pour voir que chaque livrable est a une date supperier de son livrable precedent
                        foreach($livrables as $index=>$livrable){
                            $livrable->setProject($project);

                            if($index>1){
                                if($livrables[$index]->getDate()->getTimeStamp() <= $livrables[$index-1]->getDate()->getTimestamp()){
                                    $errors=true;
                                    $this->addFlash('warning','viellez bien verifier que tous vos livrables sont due a une date supperieur a leur livrables precedants');

                                }
                            } 
                            // verification pour voir que ses livrables est supperier a aujourdhui
                            if($livrable->getDate()->getTimeStamp() <= $nowTS){
                                $errors=true;
                                $this->addFlash('warning','impossible de definir un livrable a une date qui a deja eu lieui');
                            }

                            
                        }
                    }
                    // si il y a pas d'erreur, le projet est persisté en bdd
                    if($errors==false){
                        $em->persist($project);
                        $em->flush();
                        $this->addFlash('success','votre projet a bien été énrégistré');
                        return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
                    }
                } 

            }

                

                
                return $this->render('admin/execute_project_forfait.html.twig',['project'=>$project,'form'=>$form->createView()]);
            }
        



        /**
         * @Route("/admin/{slug}/consultationClient", name="consultationClient")
         */

        public function consultationDeClients(){
            $em=$this->getDoctrine()->getManager();
            $userRepo=$em->getRepository(User::class);
            $clients=$userRepo->filterUsersByRoleClient();
            return $this->render('admin/view_modify_client.html.twig',['clients'=>$clients]);

        } 
        /**
         * @Route("/admin/{slug}/addClient",name="addClient")
         */

        public function addClient(Request $request,Slugger $slugger,UserPasswordEncoderInterface $hasher){
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            
            $client=new User();
           
            $form=$this->createForm(UserClientType::class,$client);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){ 
                $plainPassword=$client->getPassword();
                $hashedPassword=$hasher->encodePassword($client,$plainPassword);
                $client->setPassword($hashedPassword);
                $client->setIsHashed(true);
                
                
        //         $file=$form['avatar']->getData(); 
        //       $fileName = md5(uniqid()).'.'.$file->guessExtension();
        //        // Move the file to the directory where brochures are stored
        //       $file->move(
        //         $this->getParameter('brochures_directory'),
        //        $fileName
        //  );
         
        //  // Update the 'brochure' property to store the PDF file name
        //  // instead of its contents
        //    $client->setAvatar($fileName); 
           $clientRepo=$em->getRepository(User::class);
           $registeredClients=$clientRepo->filterUsersByRoleClient();
           foreach($registeredClients as $registeredClient){
               if($client->getEmail()== $registeredClient->getEmail()){
                   throw new \Exception('this email already exists');
               }
           }
            
              $client->setSlug($slugger->sluggify($client->getFirstName().' '.$client->getLastName()));
                $em->persist($client);
                $em->flush();
                $this->addFlash('success','Nouveau client enregistré');
                return $this->redirectToRoute('adminpage',['slug'=>$user->getSlug()]);
            } 
            
            return $this->render('admin/add_client.html.twig',['form'=>$form->createView()]);
        } 

        /**
         * @Route("/admin/{slug}/removeClient/{id}",name="removeClient")
         */

        public function desactivateClient($id){
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            $clientRepo=$em->getRepository(User::class);
            $client=$clientRepo->find($id);
            $client->setIsEmployed(false);
            $em->flush();
            $this->addFlash('client removed','the client has now been removed');
            return $this->redirectToRoute('adminpage',['slug'=>$user->getSlug()]);

        }  
        /**
         * @Route("admin/viewCollaborateurs/{slug}",name="viewCollaborateurs")
         */

        public function viewRHCollaborateurs(){
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            
            $RhRepo=$em->getRepository(User::class);
            $collaborateurs=$RhRepo->filterUsersByRHAndConsultant();
            return $this->render('/admin/view_modify_collaborateur.html.twig',['collaborateurs'=>$collaborateurs]);

        } 

        /**
         * @Route("/admin/{slug}/viewValidatedFormations",name="viewValidatedFormations")
         */

        public function viewValidatedFormations(){
            $em=$this->getDoctrine()->getManager();
            $formationRepo=$em->getRepository(Formation::class);
            $formations=$formationRepo->findAll();
            $this->calculateAbsentWorkingDays(Formation::class);

            return $this->render('admin/validated_formation_details.html.twig',['formations'=>$formations]);

        } 
        /**
         * @Route("/admin/{slug}/SyntheseRecapMensuel",name="viewMonthlSummaries")
         */
        public function viewMonthlySummaries(request $request){
            
            $em=$this->getDoctrine()->getManager();
            $documentRepo=$em->getRepository(MyDocument::class);
            $currentUser=$this->getUser();
            // creation d'un formualire pour trier les factures par le mois
            $form=$this->createForm(ViewFacturesByMonthType::class);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){ 

                $monthlySummaryRepo=$em->getRepository(MonthlySummary::class);

            


                // recuperation du mois saisi dans le formualire
                $formMonth=intval($form->get('month')->getViewData());
                //recuperation de l'anee saisis dans le formualire 

                $year=$form->get('year')->getViewData();
                $substrYr=substr($year,2);
                $formYear=intval($substrYr);
                // recuperation des recaps mensuel soumi dans le mois et dans l'anee
                $recaps=$monthlySummaryRepo->sortAllRecapsByUserMonthAndYear($formMonth,$formYear);

                $userRepo=$em->getRepository(User::class);
                // recuperation de tout les consultants qui sont active
                $consultants=$userRepo->findAllActiveConsultants();

                $emptyRecapConsultants=[];

                foreach($consultants as $consultant){
                    // si le consultant n'a pas soumi son recap pour le mois
                    if(empty($monthlySummaryRepo->getRecapsByUserAndMonth($consultant->getId(),$formMonth,$formYear))){
                        $emptyRecapConsultants[]=$consultant;
                    }
                }
                // afficher le nom du mois en cours selon le chiffre du mois en cours
                $monthName=$this->getActualMonthFromInt($formMonth);

                $monthWorkingDays=$this->getCurrentMonthAndWorkingDays($year,$formMonth);

                $monthWorkingDays=$monthWorkingDays['workingDays'];
                
                return $this->render('admin/view_recaps_by_month.html.twig',['recaps'=>$recaps,'emptyConsultants'=>$emptyRecapConsultants,'monthName'=>$monthName,'year'=>$substrYr,'monthWorkingDays'=>$monthWorkingDays]);
               

                
                
            }
            
            return $this->render('admin/sort_monthly_summaries.html.twig',['form'=>$form->createView()]);
        } 
                /**
         * @Route("/admin/{slug}/SyntheseArretsMamladie",name="viewSickDays")
         */
        public function viewSickDays(){
            $em=$this->getDoctrine()->getManager();
            $sickDaysSyntRepo=$em->getRepository(SickDay::class);
            $sdays=$sickDaysSyntRepo->findAll();
            $this->calculateAbsentWorkingDays(SickDay::class);
            return $this->render('admin/submitted_sick_days.html.twig',['sdays'=>$sdays]);
        }

        /**
         * @Route("/admin/{slug}/viewValidatedVacations",name="viewValidatedVacations")
         */

        public function viewValidatedVacations(){
            $em=$this->getDoctrine()->getManager();
            $vacationRepo=$em->getRepository(Vacation::class);
            $vacations=$vacationRepo->findAll();
            $this->calculateAbsentWorkingDays(Vacation::class);
            return $this->render('admin/validated_vacation_details.html.twig',['vacations'=>$vacations]);
        } 
        /**
         * @Route("/admin/{slug}/removeCollaborateur/{id}",name="removeCollaborateur")
         */

        public function desactivateCollaborateur($id){
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            $collabRepo=$em->getRepository(User::class);
            $collaborateur=$collabRepo->find($id);
            $collaborateur->setIsEmployed(false);
            $em->flush();
            $this->addFlash('warning','Compte collaborateur désactivé');
            return $this->redirectToRoute('adminpage',['slug'=>$user->getSlug()]);
        } 
        
        /**
         * @Route("/admin/{slug}/addCollaborateur",name="addCollaborateur")
         */

        public function addCollaborateur(Request $request,UserPasswordEncoderInterface $hasher,\Swift_Mailer $mailer){
            $em=$this->getDoctrine()->getManager();
            $collab=new User();
           
            $user=$this->getUser();
            //$role=new Role();
            //$role->setRoleTitle('ROLE_RH');
            //$role->setDescription('the description of role_rh');
        
            $rangeSSID=range(0,12);
            shuffle($rangeSSID);
            $ssID=implode($rangeSSID);
            $collab->setSsId($ssID);
            $form=$this->createForm(UserRHType::class,$collab);
           $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $errors=false;
                
        //       $file=$form['avatar']->getData(); 
        //       $fileName = md5(uniqid()).'.'.$file->guessExtension();
        //        // Move the file to the directory where brochures are stored
        //       $file->move(
        //         $this->getParameter('brochures_directory'),
        //        $fileName
        //  );
         
        //  // Update the 'brochure' property to store the PDF file name
        //  // instead of its contents
        //    $rh->setAvatar($fileName); 
             
            $userRepo=$em->getRepository(User::class);
            $registeredUsers=$userRepo->findAll();
            $plainPassword=$collab->getPassword();
            $encodedPassword=$hasher->encodePassword($collab,$plainPassword);
            $collab->setPassword($encodedPassword);
            $collab->setIsHashed(true);
           
            
            foreach($registeredUsers as $registeredUser){
                if($collab->getEmail() == $registeredUser->getEmail()){
                    $errors=true;
                    $this->addFlash('warning','email already in use');
                }
            }  

           
           
             $collab->setSlug($collab->getFirstName().' '.$collab->getLastName());

             if($collab->getRole()->getRoleTitle() == 'ROLE_RH'){

                $collabProjects=$collab->getConsultantProjects()->toArray();


                foreach($collabProjects as $project){
                    $collab->removeConsultantProject($project);
                 }
    
             } 

            
             

            if($errors == false){ 
                // envoie de message bienvenue au nouveau consultant

              $collabEmail=$collab->getEmail();
             
               $message=(new \Swift_Message('Bienvenue chez VBL'))
               ->setFrom('reubenchouraki@vbladvisory.com')
               ->setTo($collabEmail)
               ->setBody($this->renderView('security\bienvenue.html.twig',['collab'=>$collab]),'text/html'); 
                
                $mailer->send($message);
                
            
              $em->persist($collab);
             


            $this->addFlash('success','le nouveau collaborateur vient d\'etre ajouté');
            $em->flush();
            return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
         }
         
     }

     return $this->render('admin/ajout_rh.html.twig',['form'=>$form->createView()]);
    }



         /**
         * @Route("/admin/{slug}/modifyCollaborateur/{id}", name="modifyCollaborateur")
         */
        public function modifyCollaborateur(Request $request,$id,UserPasswordEncoderInterface $hasher){ 
            $em=$this->getDoctrine()->getManager();
            $user=$this->getUser();
            $collabRepo=$em->getRepository(User::class);
            $collaborateur=$collabRepo->find($id);
            $form=$this->createForm(UserRHType::class,$collaborateur);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $password=$collaborateur->getPassword();
                $hashedPassword=$hasher->encodePassword($collaborateur,$password);
                $collaborateur->setPassword($hashedPassword);
               
                $em->flush();
                $this->addFlash('warning','Modification du profil collaborateur effectuée');
                return $this->redirectToRoute('adminpage',['slug'=>$user->getSlug()]);
            } 
            return $this->render('admin/modify_rh.html.twig',['form'=>$form->createView()]);

        } 
        
        /**
         * @Route("admin/{slug}/modifyConsultantVacations/{consultantId}", name="modifyConsultantVacations")
         */
        
        public function seeConsultantVacationsToModify($consultantId){
            $user=$this->getUser();
            $em=$this->getDoctrine()->getManager();

            $userRepo=$em->getRepository(User::class);
            $vacationRepo=$em->getRepository(Vacation::class);

            $consultant=$userRepo->find($consultantId);
            // triage des vaccances qui correspondent au consultant choisi en parametre
            $consultantVacations=$vacationRepo->findBy(['consultant'=>$consultant->getId()]);

            return $this->render('admin/modify_consultant_vacations.html.twig',['consultantVacations'=>$consultantVacations,'consultant'=>$consultant]);
        }

        /**
         * @Route("admin/{slug}/modifyConsultantVacation/{id}",name="modifyConsultantVacation")
         */

        public function modifyConsultantVacations($id, Request $request){
            $user=$this->getUser();

            $em=$this->getDoctrine()->getManager();

            $vacationRepo=$em->getRepository(Vacation::class);
            //cibler la vaccance choisi
            $vacation=$vacationRepo->find($id);

            $form=$this->createForm(VacationType::class,$vacation);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $em->persist($vacation);
                // calculer le nombre d'absence ouvré pour la congé modifié
                $this->calculateAbsentWorkingDays(Vacation::class,$vacation);
                $this->addFlash('success','la congé a bien été modifié');
                return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
            }

            return $this->render('admin/modify_consultant_vacation.html.twig',['form'=>$form->createView()]);
        } 

        /**
         * @Route("admin/{slug}/viewConsultantFormations/{consultantId}", name="viewConsultantFormations")
         */


        public function seeConsultantFormationsToModify($consultantId){
            $user=$this->getUser();
            $em=$this->getDoctrine()->getManager();

            $consultantRepo=$em->getRepository(User::class);

            $formationsRepo=$em->getRepository(Formation::class);

            // trier le formations en fonction du consultant choisi en parametre
            $consultant=$consultantRepo->find($consultantId);

            $consultantFormations=$formationsRepo->findBy(['consultant'=>$consultant->getId()]);

            return $this->render('admin/view_consultant_formations.html.twig',['consultantFormations'=>$consultantFormations,'consultant'=>$consultant]);
        } 

        /**
         * @Route("admin/{slug}/modifyFormation/{id}", name="modifyFormation")
         */

        public function modifyConsultantFormation($id,Request $request){
            $user=$this->getUser();

            $em=$this->getDoctrine()->getManager();

            $formationsRepo=$em->getRepository(Formation::class);
            // cibler la formation choisi
            $formation=$formationsRepo->find($id);

            $form=$this->createForm(FormationType::class,$formation);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){ 
                $em->persist($formation);
                // calculer le nombre d'absnece ouvré pour la formation choisi
                $this->calculateAbsentWorkingDays(Formation::class,$formation);
                $this->addFlash('success','la formation a bien ete modifié');
                return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
            }

            return $this->render('admin/modify_consultant_formation.html.twig',['form'=>$form->createView()]);
        } 

        

     

        /**
         * @route("/admin/{slug}/modifyClient/{id}",name="modifyClient")
         */
         public function modifyClient(Request $request,$id,UserPasswordEncoderInterface $hasher){
             $em=$this->getDoctrine()->getManager();
             $user=$this->getUser();
             $clientRepo=$em->getRepository(User::class);
             $client=$clientRepo->find($id);
             $form=$this->createForm(UserClientType::class,$client);
             $form->handleRequest($request);
             if($form->isSubmitted() && $form->isValid()){
                 $password=$client->getPassword();
                 $hashedPassword=$hasher->encodePassword($client,$password);
                 $client->setPassword($hashedPassword);
                
                 $em->flush();
                 $this->addFlash('warning','Modification du profil client effectuée');
                 return $this->redirectToRoute('adminpage',['slug'=>$user->getSlug()]);
             }

             return $this->render('admin/modify_client.html.twig',['form'=>$form->createView()]);
         } 
         /**
          * @Route("/admin/{slug}/viewClientFeedback",name="viewClientFeedback")
          */

          

         public function viewClientFeedback(Request $request){
            $em=$this->getDoctrine()->getManager();
            $currentUser=$this->getUser();
            // creation d'une entité qui a pour seul but d'etre lié a un consultant pour pouvoir recuperé un consultant en bdd, sinon pour une raison inconnu, il est impossible de recuperer un consultant a partir d'un formualire si ce consultant n'est pas lié a une entité meme si c'est une entité bidon
            $filterConge=new FilterConge();

            $form=$this->createForm(ChooseAbsenceFilterType::class,$filterConge);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $userRepo=$em->getRepository(User::class);
                
                $consultantId=intval($form->get('consultant')->getViewData());

                $consultant=$userRepo->find($consultantId);
                
                $feedbackRepo=$em->getRepository(Feedback::class);
                $consultantFeedbacks=$feedbackRepo->findBy(['consultant'=>$consultantId]);
                return $this->render('admin/view_client_feedback.html.twig',['consultantFeedbacks'=>$consultantFeedbacks,'consultant'=>$consultant]);
            }

            return $this->render('admin/choose_feedback_consultant.html.twig',['form'=>$form->createView()]);
            
         } 

         /**
          * @Route("/admin/{slug}/viewDesactivatedUsers",name="viewDesactivateUsers")
          */
         public function viewDesactivatedCollaborateurs(){
             $em=$this->getDoctrine()->getManager();
             $userRepo=$em->getRepository(User::class);
             $desactivatedRH=$userRepo->filterDesactivatedRoleRh();
             return $this->render('admin/view_desactivated_rh.html.twig',['desactivatedRH'=>$desactivatedRH]); 
         }
         
         /**
          * @Route("/admin/{slug}/reactivateCollaborateur/{id}", name="reactivateCollaborateur")
          */

         public function reactivateCollaborateur($id){
             $em=$this->getDoctrine()->getManager();
             $user=$this->getUser();
             $userRepo=$em->getRepository(User::class);
             $rhToReactivate=$userRepo->find($id);
             $rhToReactivate->setIsEmployed(true);
             $em->flush();
             $this->addFlash('success','Réactivation du profil collaborateur effectuée');
             return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
         } 

       

         /** 
         * @Route("/admin/{slug}/seeForfaitDetails/{id}", name="seeForfaitDetails")
          */

         public function seeDetailsProjectForfait($id){
             $em=$this->getDoctrine()->getManager();
             $projectRepo=$em->getRepository(Project::class);

             $project=$projectRepo->find($id);
             return $this->render('admin/details_projet_forfait.html.twig',['project'=>$project]);
             

         } 

         /**
          * @Route("/admin/{slug}/seeRegiesDetails/{id}", name="seeRegisDetails")
          */


         public function seeRegisDetails($id){
             $em=$this->getDoctrine()->getManager();
             $projectRepo=$em->getRepository(Project::class);
             $project=$projectRepo->find($id);
             return $this->render('admin/details_projet_regis.html.twig',['project'=>$project]);
         }

         /**
          * @Route("admin/{slug}/modifyProject/{id}", name="modifyProject")
          */

         public function modifyProject($id, Request $request){
             $em=$this->getDoctrine()->getManager();
             $projectRepo=$em->getRepository(Project::class);
             $project=$projectRepo->find($id);
             
             $form=$this->createForm(ProjectType::class,$project);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

            }

            return $this->render('admin/modify_project.html.twig',['form'=>$form->createView()]);
         } 

         /**
          * @Route("admin/viewAMByConsultant/{consultantId}", name="viewAMByConsultant")
          */

         public function selectAMToView($consultantId){
            $user = $this->getUser();

            $em=$this->getDoctrine()->getManager();

            $userRepo=$em->getRepository(User::class); 

            $consultant=$userRepo->find($consultantId);

            $amRepo=$em->getRepository(SickDay::class);

            $userArretsMaladies=$amRepo->findBy(['consultant'=>$consultantId]);

            return $this->render('admin/view_consultant_am.html.twig',['userArretsMaladies'=>$userArretsMaladies,'consultant'=>$consultant]);


         }

         /**
          * @Route("admin/{slug}/modifyConsultantAM/{id}", name="modifyConsultantAM")
          */
         public function modifyConsultantAm($id,Request $request){
             $user = $this->getUser();

             $em=$this->getDoctrine()->getManager();

             $amRepo=$em->getRepository(SickDay::class);

             $amToModify=$amRepo->find($id); 

             $form=$this->createForm(SickDayType::class,$amToModify);

             $form->handleRequest($request);

             if($form->isSubmitted() && $form->isValid()){
                $em->persist($amToModify);
                $em->flush();
                $this->calculateAbsentWorkingDays(SickDay::class,$amToModify);
                $this->addFlash('success','arret maladie modifié');
                return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
             }      

             return $this->render('admin/modify_consultant_am.html.twig',['form'=>$form->createView()]);


             
         }

         /**
          * @Route("admin/{slug}/seeInitialDocuments", name="seeInitialDocuments")
          */

         public function viewConsultantInitialDocuments(Request $request){
             $user=$this->getUser();

             $em=$this->getDoctrine()->getManager();


             $filterConge=new FilterConge();

             $form=$this->createForm(ChooseAbsenceFilterType::class,$filterConge);

             $form->handleRequest($request);

             if($form->isSubmitted() && $form->isValid()){ 

                $em=$this->getDoctrine()->getManager();

                $consultantIdStr=$form['consultant']->getViewData();

                $consultantId=intval($consultantIdStr);

                $userRepo=$em->getRepository(User::class);

                $consultant=$userRepo->find($consultantId);

                $documentRepo=$em->getRepository(MyDocument::class);
                // trier les documents par le consultant choisi dans le formualire
                $consultantDocuments=$documentRepo->findBy(['category'=>'documents initialisation','consultant'=>$consultant->getId()]);

                return $this->render('admin/view_initial_documents.html.twig',['consultantDocuments'=>$consultantDocuments,'consultant'=>$consultant]);

                
             }

             return $this->render('admin/initial_documents_consultant_form.html.twig',['form'=>$form->createView()]);


         }

    

        





}