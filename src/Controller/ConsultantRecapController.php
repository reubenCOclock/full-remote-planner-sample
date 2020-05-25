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
    use App\Form\ViewFacturesByMonthType;
    use App\Utils\Slugger;
    use App\Entity\Feedback;
    use App\Entity\SickDay;
    use App\Form\AccountType;
    use App\Entity\MonthlySummary;
    use App\Form\FacturationFormType;
    use App\Form\EmptyRecapConsultantType;
    use App\Entity\MyDocument;
    use App\Form\PasswordUpdateType;
    use Doctrine\Common\Collections\Collection;
    use Dompdf\Dompdf;
    use Dompdf\Options;
    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use App\Repository\UserRepository;
    use App\Entity\ProjectDays;
    use App\Form\MonthlySummaryType;
    use App\Form\RecapErrorChoiceType;

    class ConsultantRecapController extends ConsultantAbsenceController {


        /**
         * Permet de déclarer un récapitulatif mensuels
         *
         * @Route("user/{slug}/recapMensuel",name="monthlySummary")
         *
         */
        public function recapMensuel(Request $request, ObjectManager $manager,$slug, UserRepository $repo) { 

            $currentUser=$this->getUser();


            $session= new Session();

            $session->set('equalDays',true);

            $monthlySumm = new MonthlySummary();

            $monthlySumm->setConsultant($currentUser);
           
            
            
            // si le consultant est attribué des projets
            if(!empty($currentUser->getConsultantProjects()->toArray())){
                $consultantProjects=$currentUser->getConsultantProjects()->toArray();
                // je choisi le premier projet active qui est attribué au consultant
                $consultantProject=$consultantProjects[0];
               // je cree une entité representant le sous formualire
                $projectDays=new ProjectDays();
                $projectDays->setProject($consultantProject);
                $projectDays->setDays(0);
                //j'affiche les champs du formulaire
                $monthlySumm->addProjectDay($projectDays);
            }

            
           
    
           

           
            $form = $this->createForm(MonthlySummaryType::class, $monthlySumm);


            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) { 
                // je recupere les projets soumis
                $projectDays=$monthlySumm->getProjectDays();

                $now=new \DateTIme();
                $dateMonthFormat=$now->format('m');
                $dateYearFormat=$now->format('y');

                $intValDateMonth=intval($dateMonthFormat);
                $intValDateYear=intval($dateYearFormat);
                $monthlySumm->setYear($intValDateYear);

                // je calcule le jour d'absence ouvré pour ce consultant dans le mois 

                $grantedAbsenceDays=$this->getTotalConsultantAbsencesPerMonth();

                

                $monthlySumm->setConsultantAbsenceDays($grantedAbsenceDays);

                // j'inistalise le variable de test a faux
                $differentMonth=false;
                $sameProject=false;
                $unequalWorkingDays=false;

               
                // je calcule le nombre de jours ouvré dans le mois
               $currentMonthWorkingDays=$this->getCurrentMonthAndWorkingDays();

                // si le mois saisi n'est pas le mois en cours
                if($monthlySumm->getMonth()!=$intValDateMonth){
                    $differentMonth=true;
                    $this->addFlash('warning','vous declaré le mauvais mois,vieullez declarer le mois dans lequel on se trouve actuellement');
                   
                  

                }





                if($differentMonth==false){

                $totalDays=0;
                // je boucle sur les projets decalrés
                foreach($projectDays as $index=>$project){
                    // j'addtione les jours travaillés sur chaque projet
                    if($project->getDays()!=null){
                        $totalDays=$totalDays+$project->getDays();


                    }
                
                    if($index!=0){
                        // si les projets sont identique je lance une erreur
                        if($projectDays[$index]->getProject()->getTitle()==$projectDays[$index-1]->getProject()->getTitle()){
                            $sameProject=true;
                            $this->addFlash('warning','vous avez choisi des projets identiques, vieullez choisir un projet different lors d\'une declaration d\'un nouveaau projet pour un recapulatif mensuel');

                        }
                    }

                }
            }
            // si cest le bon mois et les projets sont bon
            if($differentMonth==false && $sameProject==false){
                // calcul de nb de jours attendu
                $expectedWorkingDays=$currentMonthWorkingDays['workingDays']-$grantedAbsenceDays;
                // calcuk de la difference
                $actualWorkingDaysDifference=$totalDays-$expectedWorkingDays;
              
                
                if($actualWorkingDaysDifference > 0){

                    $unequalWorkingDays=true;
                }

                else if($actualWorkingDaysDifference < 0){

                    $unequalWorkingDays=true;
                }
                // si les jours declarés ne correspondent pas au nb de jours travaillés
                if($unequalWorkingDays==true){ 
                    // creation d'une session pour stocké les infos du recap
                $session=new Session();
                $session->set('expectedWorkingDays',$expectedWorkingDays);
                
                $session->set('monthlySumm',$monthlySumm);
                $session->set('totalDays',$totalDays);
                $session->set('projects',$monthlySumm->getProjectDays()->toArray());
               
                $consultantProjects=[];

                foreach($session->get('projects') as $index=>$consultantProject){

                    //$consultantProject[$index]['objProject']=$consultantProject;
                    $consultantProjects[$index]['project']=$consultantProject->getProject()->getTitle();
                    $consultantProjects[$index]['days']=$consultantProject->getDays();
                }




                $session->set('consultantProjects',$consultantProjects);

                
               if($actualWorkingDaysDifference > 0){
                   $difference=$actualWorkingDaysDifference;
                   $session->set('difference',$difference);
                   $session->set('plus','plus');
               }

               else if($actualWorkingDaysDifference < 0){
                   $difference=abs($actualWorkingDaysDifference);
                   $session->set('difference',$difference);
                   $session->set('moins','moins');
               }

               // je fais appel a la route qui fait appel a la fonction showRecapAbsenceError

                return $this->redirectToRoute('showRecapError',['slug'=>$currentUser->getSlug()]);

            }




            }
             // si on arrive ici ca veux dire que la fonction n'a rien retourné donc forcement le nombre de jours travaillés correpond aux absence ouvré
                if($differentMonth==false && $sameProject==false){

                    $monthlySumm->setYear($intValDateYear);
                    
                    $monthlySumm->setTotalDays($totalDays);
                   
                    $manager ->persist($monthlySumm);
                   
                   
                    $manager ->flush();

                  

                    $this->addFlash(
                        'success', "Ton récapitulatif mensuel a bien été enregistré!");
                        $user = $this->getUser();

                        return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);



                }
            }

            return $this->render('consultant/monthly_summary.html.twig', [
                'form'=>$form->createView(),'workingDaysMonth'=>$this->getCurrentMonthAndWorkingDays(),'grantedAbsenceDays'=>$this->getTotalConsultantAbsencesPerMonth()
            ]);
        }

    





         /**
            * @Route("user/resolveUnequalDays", name="resolveUnequalDays")
          */

           public function executeUnequalDaysChoice(Request $request){

            $now=new \DateTIme();
            $currentMonthIntVal=intval($now->format('m'));
            $intValDateYear=intval($now->format('y'));

            $session=new Session();

            $em=$this->getDoctrine()->getManager();

            $errors=false;
            // creation d'un objet recapMensuel
            $monthlySumm = new MonthlySummary();


            $currentUser=$this->getUser();
            
            $projectRepo=$em->getRepository(Project::class);
            // recuperation des informations concernant les projets des consultants dans le recap stocké en session sous forme de tableau
            foreach($session->get('consultantProjects') as $consultantProject){ 
                // creation de la nouvelle entité representant le sous formulaire
                $projectDays=new ProjectDays(); 
                // je boucle sur les informations du projet en cours
                foreach($consultantProject as $index=>$value){
                    if($index=='project'){
                        // je recupere le projet
                        $projectToFind=$projectRepo->findProjectByTitle($value);
                        // je l'ajoute a l'entité
                        $projectDays->setProject($projectToFind[0]);
                    }

                    if($index=='days'){
                        // j'ajoute les nombres de jours travaillé sur le projet a l'entité
                        $projectDays->setDays($value);
                    }
                }
                // j'ajoute l'entité projectDays au recapMensuel
                $monthlySumm->addProjectDay($projectDays);
            }







            $monthlySumm->setConsultant($currentUser);



            $monthlySumm->setMonth($currentMonthIntVal);
            $monthlySumm->setYear($intValDateYear);

            $monthlySumm->setConsultantAbsenceDays($this->getTotalConsultantAbsencesPerMonth());

            $session->set('acutalMonth',$this->getActualMonthFromInt($session->get('month')));


            
            // creation d'un formualire avec les donéés deja alimenté du recapMensuel

            $form = $this->createForm(MonthlySummaryType::class, $monthlySumm);

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

           
              // si les mois sont differentes
               if($monthlySumm->getMonth()!=$currentMonthIntVal){
                   $errors=true;
                   $this->addFlash('warning','viellez saisir le mois en cours');
                  
               }
               $totalDays=0;
               
               $projectDays=$form->get('projectDays')->getViewData();
               foreach($projectDays as $projectDay){
                   
                    $totalDays=$totalDays+$projectDay->getDays();
               }

               
               // si le nombre des jours declarés est en decalage avec le nombre de jours attendu 
              if($totalDays!=$session->get('expectedWorkingDays')){
                  $errors=true;
                  $this->addFlash('warning','vieullez bien saisir le numero de jours qui correspon au nombre de jours travaillé ce mois:'.' '.$session->get('expectedWorkingDays'));
              }

              // si tout va bien
                if($errors==false){

                 $em->persist($monthlySumm);

                 $em->flush();
                 $this->addFlash('success','ton recap a bien été énregistré');
                 return $this->redirectToRoute('userpage',['slug'=>$currentUser->getSlug(),]);
                }

            }



            return $this->render('consultant/resolve_unequal_days.html.twig',['form'=>$form->createView(),'expectedWorkingDays'=>$session->get('expectedWorkingDays')]);



           } 

           /**
            * @Route("user/{slug}/showRecapError", name="showRecapError")
            */


           public function showRecapAbsenceError(Request $request){
                $user=$this->getUser();
                // formualire qui donne le consultant le choix de continueur ou non malgré le decalage entre les jours travaillés et les jours d'absences
                $form=$this->createForm(RecapErrorChoiceType::class);
                $form->handleRequest($request);

        

                if($form->isSubmitted() && $form->isValid()){ 
                    // si le consultant decide de continuer comme tel 
                if($form->get('yes')->isClicked()){
                        $now=new \DateTIme();
                        $currentMonthIntVal=intval($now->format('m'));
                        $intValDateYear=intval($now->format('y'));
            
                        $session=new Session();
            
                        $em=$this->getDoctrine()->getManager();
            
                        $errors=false;
                        // creation d'un recap
                        $monthlySumm = new MonthlySummary();


            
            
                        $currentUser=$this->getUser();
                     
                        $projectRepo=$em->getRepository(Project::class);
                        // recuperation des informations par rapport au projects declaré dans le recap mensuel
                        foreach($session->get('consultantProjects') as $consultantProject){ 
                            // creation a nouveau de l'entité projetDays
                            $projectDays=new ProjectDays();
                            
                            foreach($consultantProject as $index=>$value){
                                // si l'index du tableau stocké en session correspond a 'project'
                                if($index=='project'){ 
                                    // je recupere le projet
                                    $projectToFind=$projectRepo->findProjectByTitle($value);
                                    // je l'attribue a l'entité proejctDays
                                    $projectDays->setProject($projectToFind[0]);
                                }
                                // j'attribué le nombre de jours travaillé sur le projet
                                if($index=='days'){
                                    $projectDays->setDays($value);
                                }
                            }
                            // j'ajoute la sous entité a l'entité monthlySummary
                            $monthlySumm->addProjectDay($projectDays);
                        }
            
            
            
            
            
            
                        // je procede, persite et flush
                        $monthlySumm->setConsultant($currentUser);
            
            
            
                        $monthlySumm->setMonth($currentMonthIntVal);
                        $monthlySumm->setYear($intValDateYear);

                        $monthlySumm->setConsultantAbsenceDays($this->getTotalConsultantAbsencesPerMonth());
            
                         $monthlySumm->setTotalDays($session->get('totalDays'));
                     

                         $em->persist($monthlySumm);
                         $em->flush();
                         $this->addFlash('success','ton recap a bien été énregistré');
                         return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
                       }
                       // si le consultant ne veux pas contineur avec ce decalage, il est redirigé a la route qui fait appel a la fonction executeUnequalDaysChoice
                       else if($form->get('no')->isClicked()){
                           return $this->redirectToRoute('resolveUnequalDays');
                       }
                }

                return $this->render('consultant/execute_recap_error_choice.html.twig',['form'=>$form->createView()]);


           }




    }



?>
