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


class ConsultantUtilsController extends AbstractController {


    public function getActualMonthFromInt($int){
        switch($int){
            case 1:
            $int='Janvier';
            break;
            case 2: 
            $int='Fevrier';
            break;
            case 3: 
            $int='Mars';
            break;
            case 4: 
            $int='Avril';
            break;
            case 5: 
            $int='Mai';
            break;
            case 6:
            $int='Juin';
            break;
            case 7: 
            $int='Juillet';
            break;
            case 8:
            $int='Aout';
            break;
            case 9:
            $int='Septembre';
            break;
            case 10:
            $int='Octobre';
            break;
            case 11:
            $int='Novembre';
            break;
            case 12:
            $int='Decembre';
            break;

        } 

        return $int;
    } 


    function isNotWorkable($date)
	{

	  	if ($date === null)
	  	{
	    	$date = time();
	  	}

         $date = strtotime(date('m/d/Y',$date));
       

         $year = date('Y',$date); 
        
        
       

		$easterDate  = easter_date($year);
		$easterDay   = date('j', $easterDate);
		$easterMonth = date('n', $easterDate);
		$easterYear   = date('Y', $easterDate);

		$holidays = array(
	    // Dates fixes
	    mktime(0, 0, 0, 1,  1,  $year),  // 1er janvier
	    mktime(0, 0, 0, 5,  1,  $year),  // Fête du travail
	    mktime(0, 0, 0, 5,  8,  $year),  // Victoire des alliés
	    mktime(0, 0, 0, 7,  14, $year),  // Fête nationale
	    mktime(0, 0, 0, 8,  15, $year),  // Assomption
	    mktime(0, 0, 0, 11, 1,  $year),  // Toussaint
	    mktime(0, 0, 0, 11, 11, $year),  // Armistice
	    mktime(0, 0, 0, 12, 25, $year),  // Noel

	    // Dates variables
	    mktime(0, 0, 0, $easterMonth, $easterDay + 1,  $easterYear),
	    mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear),
	    mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear),
        );
        
        
  
  	return in_array($date, $holidays);
    }



    /**
     * @Route("user/testDays/",name="testDays")
     */
    
    public function getCurrentMonthAndWorkingDays(){
        
        $currentDate=new \DateTime();
        $currentMonth=$currentDate->format('m');
        $currentMonthIntVal=intval($currentMonth);
  
        $currentYear=$currentDate->format('Y');
        
     
        $firstDayOfMonth=1;
        $lastDayOfMonth=intval($currentDate->format('t'));
      
        // recuperation du mois en cours
        $actualMonth=$this->getActualMonthFromInt($currentMonthIntVal);

        $workingDays=0;
        // je boucle du 1er au dernier jours du mois
        for($i=$firstDayOfMonth;$i<=$lastDayOfMonth;$i++){
            $dateInLoop=new \DateTime($currentYear.'/'.$currentMonth.'/'.$i); 
            // si cest pas un wkend
            if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                $strFormat=$dateInLoop->format('Y/m/d');
                $intFormat=strtotime($strFormat);
                // si cest pas ferié
                if(!$this->isNotWorkable($intFormat)){
                    // j'alimente les absences decalrés par 1 
                    $workingDays=$workingDays+1;
                }
            }
        } 
        // je retroune un tableu qui contient le mois en cours et le nb de jours ouvré dans ce mois
        return['actualMonth'=>$actualMonth,'workingDays'=>$workingDays];
    }


    public function calculateAbsentWorkingDays($targetedEntity,$id=null,$entity=null){
        $em=$this->getDoctrine()->getManager();
        // je recupere le repository de l'entité ciblé
        $targetedEntityRepo=$em->getRepository($targetedEntity); 
        // si un id est passé en parametre
        if($id!=null){ 
            // je recupere les resultats trier pas le consultant
          $entityDatas=$targetedEntityRepo->findBy(['consultant'=>$id]); 
        } 
        // si l'id est null, l'entité est passé en parametre directement
        else{
            $entityDatas=[$entity];
        }
    
        if($targetedEntity==Vacation::class){ 
            
           // je boucle sur les vaccanes
            foreach($entityDatas as $entityData){
                $grantedAbsences=0;
                $startDate=$entityData->getStartDate();
                $endDate=$entityData->getEndDate();
                // je recupere la date de debut
                $startDateMonth=$startDate->format('m');
                // je recupere la date de fin
                $endDateMonth=$endDate->format('m');
    
                $startDateYear=$startDate->format('Y');
                $endDateYear=$endDate->format('Y');


                // si la date de debut et la date de fin sont les memes 
    
                if($startDateMonth == $endDateMonth){
                    $startDay=intval($startDate->format('d'));
                    $endDay=intval($endDate->format('d'));
    
                    // je boucle sur chaque date
                    for($i=$startDay;$i<=$endDay;$i++){
                        $dateInLoop= new \DateTime($startDateYear.'/'.$startDateMonth.'/'.$i);
                        // si cest pas un wkend
                        if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                            $strFormat=$dateInLoop->format('Y/m/d');
                            $intFormat=strtotime($strFormat);
    
                            if(!$this->isNotWorkable($intFormat)){ 
                                // si cest pas ferire j'incremente les absences par 1
                                $grantedAbsences=$grantedAbsences+1;
                            }
                        }
                    }
                }
                
                // si cest pas dans le meme mois 
                else if($startDateMonth!=$endDateMonth){
                    // si cest dans la meme anée
                    if($startDateYear == $endDateYear){
                        // si la congé saute un mois 
                       if($endDateMonth - $startDateMonth >1){
                          // pour les mois qui se trouvent entre la date de fin et date de debut
                           for($i=intval($startDateMonth+1);$i<=intval($endDateMonth-1);$i++){
                              $monthDate=new \DateTime($startDateYear.'/'.$i.'/01');
                              // je boucle sur chaque journée qui se trouve dans le mois en cours
                              for($j=1;$j<=$monthDate->format('t');$j++){ 
                                  
                                  $dateInLoop = new \DateTime($startDateYear.'/'.$i.'/'.$j);
                                  
                                    // si cest pas un wkend
                                  if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){ 
                                     
                                    $strFormat=$dateInLoop->format('Y/m/d');
                                    $intFormat=strtotime($strFormat);
                                    // si cest pas ferier
                                    if(!$this->isNotWorkable($intFormat)){
                                        // j'incremente les absences par 1 
                                        $grantedAbsences=$grantedAbsences+1;
                                        
                                    }
                                }

                              }

                              // je recupere la date de debut
                              $startDayV1=intval($startDate->format('d'));
                              // la date de fin correspond au dernier jours du mois de la date de debut
                              $endDayV1=intval($startDate->format('t'));
                              // je boucle sur les dates
                              for($i=$startDayV1;$i<=$endDayV1;$i++){
                                  $dateInLoop= new \DateTime($startDateYear.'/'.$startDateMonth.'/'.$i);
                                  // si cest pas un wkend
                                  if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){ 
                                      
                                      $strFormat=$dateInLoop->format('Y/m/d');
                                      $intFormat=strtotime($strFormat);
              
                                      if(!$this->isNotWorkable($intFormat)){
                                          // ssi cest pas ferié j'incremente les absences par 1
                                          $grantedAbsences=$grantedAbsences+1;
                                      }
                                  }
                              } 
              
                              $startDayV2=1;
                              // je cible le dernier jours de la congé
                              $endDayV2=$endDate->format('d');
                              // je boucle dessus
                              for($i=0;$i<=$endDayV2;$i++){
                                  $dateInLoop=new \DateTime($endDateYear.'/'.$endDateMonth.'/'.$i);
                                // si cest pas un wkend
                                  if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                     
                                      $strFormat=$dateInLoop->format('Y/m/d');
                                      $intFormat=strtotime($strFormat);
              
                                      if(!$this->isNotWorkable($intFormat)){
                                          // si cest pas ferié j'incremente les absences par 1
                                          $grantedAbsences=$grantedAbsences+1;
                                      }
                                  }
                              }
                           } 



                       }  

                       // si la congé ne saute pas de mois

                       else{ 

                        // je cible le premier jour de la congé
                        $startDayV1=intval($startDate->format('d'));
                        // le dernier jour correspond au derneir jours du mois
                        $endDayV1=intval($startDate->format('t'));
                        // je boucle
                        for($i=$startDayV1;$i<=$endDayV1;$i++){
                            $dateInLoop= new \DateTime($startDateYear.'/'.$startDateMonth.'/'.$i);
                            if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                $strFormat=$dateInLoop->format('Y/m/d');
                                $intFormat=strtotime($strFormat);
        
                                if(!$this->isNotWorkable($intFormat)){
                                    $grantedAbsences=$grantedAbsences+1;
                                }
                            }
                        } 
                        // je cible le 1er jours du mois
                        $startDayV2=1;
                        // le dernier jours corespond au dernier jours de la congé
                        $endDayV2=$endDate->format('d');
                        // je boucle
                        for($i=0;$i<=$endDayV2;$i++){
                            $dateInLoop=new \DateTime($endDateYear.'/'.$endDateMonth.'/'.$i);
        
                            if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                $strFormat=$dateInLoop->format('Y/m/d');
                                $intFormat=strtotime($strFormat);
        
                                if(!$this->isNotWorkable($intFormat)){
                                    $grantedAbsences=$grantedAbsences+1;
                                }
                            }
                        }

                       }
                    } 

                    // si le debut et la fin de la congé ne sont pas dans la meme anée
                    else{ 
                        // si il y a un seul mois de difference
                        if($startDateMonth - $endDateMonth ==11){

                           
                            $startDayV1=intval($startDate->format('d'));
                            // le dernier jours correspond au dernier jours du mois
                            $endDayV1=intval($startDate->format('t'));
                            // je boucle
                            for($i=$startDayV1;$i<=$endDayV1;$i++){
                                $dateInLoop= new \DateTime($startDateYear.'/'.$startDateMonth.'/'.$i);
                                if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                    $strFormat=$dateInLoop->format('Y/m/d');
                                    $intFormat=strtotime($strFormat);
            
                                    if(!$this->isNotWorkable($intFormat)){
                                        $grantedAbsences=$grantedAbsences+1;
                                    }
                                }
                            } 
            
                            $startDayV2=1;
                            // le dernier jour correspond a la date de fin de la congé
                            $endDayV2=$endDate->format('d');
                            // je boucle
                            for($i=$startDayV2;$i<=$endDayV2;$i++){
                                $dateInLoop=new \DateTime($endDateYear.'/'.$endDateMonth.'/'.$i);
            
                                if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                    $strFormat=$dateInLoop->format('Y/m/d');
                                    $intFormat=strtotime($strFormat);
            
                                    if(!$this->isNotWorkable($intFormat)){
                                        $grantedAbsences=$grantedAbsences+1;
                                    }
                                }
                                
                            }
                        } 
                        // si la congé saute un mois entier
                        else{ 
                            // je prends les dates correspondant au debut de l'anée 
                            
                            for($i=$startDateMonth+1;$i<=12;$i++){
                                $dateToUse =new \DateTime($startDateYear.'/'.$i.'/01');
                                $daysInDate=$dateToUse->format('t');
                                // je boucle sur le mois entier
                                for($j=1;$j<=$daysInDate;$j++){
                                    $dateToCheck=new \DateTime($endDateYear.'/'.$i.'/01');
                                    for($j=1;$j<$dateToCheck->format('t');$j++){
                                        $dateInLoop = new \DateTime($startDateYear.'/'.$i.'/'.$j);

                                        if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                          $strFormat=$dateInLoop->format('Y/m/d');
                                          $intFormat=strtotime($strFormat);
                  
                                          if(!$this->isNotWorkable($intFormat)){
                                              $grantedAbsences=$grantedAbsences+1;
                                          }
                                      }
                                    }
                                }
                            }
                            // si la congé prends fin apres janvier
                            if(intval($endDateMonth>1)){ 
                                // je boucle sur le mois de janvier et chaque mois apres
                                for($i=1;$i<=intval($endDateMonth-1);$i++){
                                    $dateToCheck=new \DateTime($endDateYear.'/'.$i.'/01');
                                    for($j=1;$j<$dateToCheck->format('t');$j++){
                                        $dateInLoop = new \DateTime($startDateYear.'/'.$i.'/'.$j);

                                        if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                          $strFormat=$dateInLoop->format('Y/m/d');
                                          $intFormat=strtotime($strFormat);
                  
                                          if(!$this->isNotWorkable($intFormat)){
                                              $grantedAbsences=$grantedAbsences+1;
                                          }
                                      }
                                    }

                                }
                            } 

                            // je prends la date de debut
                            $startDayV1=intval($startDate->format('d'));
                            // je prends le dernier jours du mois avec la date de debut
                            $endDayV1=intval($startDate->format('t'));
                            // je boucle
                             for($i=$startDayV1;$i<=$endDayV1;$i++){
                            $dateInLoop= new \DateTime($startDateYear.'/'.$startDateMonth.'/'.$i);
                                if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                $strFormat=$dateInLoop->format('Y/m/d');
                                $intFormat=strtotime($strFormat);
    
                            if(!$this->isNotWorkable($intFormat)){
                                $grantedAbsences=$grantedAbsences+1;
                            }
                        }
                    } 
                    
                    $startDayV2=1;
                    // je prends la date de fin
                    $endDayV2=$endDate->format('d');
                    // je boucle
                    for($i=0;$i<=$endDayV2;$i++){
                        $dateInLoop=new \DateTime($endDateYear.'/'.$endDateMonth.'/'.$i);
    
                        if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                            $strFormat=$dateInLoop->format('Y/m/d');
                            $intFormat=strtotime($strFormat);
    
                            if(!$this->isNotWorkable($intFormat)){
                                $grantedAbsences=$grantedAbsences+1;
                            }
                        }
                        
                    }


                        }
                    }
                   
                }
                
               
                $entityData->setGrantedAbsenceDays($grantedAbsences);
            
                $em->persist($entityData);

                // meme loqique de tratement avec les sous congés sans le traitement des cas ou l'on saute un mois car la durée maximum d'une sous congé est 30 jours
                $subVacations=$entityData->getSubVacations()->toArray();



                foreach($subVacations as $subVacation){

                    $grantedAbsences=0;
                    $startDate=$subVacation->getStartDate();
                    $endDate=$subVacation->getEndDate(); 

                    $startDateMonth=$startDate->format('m');
                    $endDateMonth=$endDate->format('m');

                    $startDateYear=$startDate->format('Y');
                    $endDateYear=$endDate->format('Y');

                    if($startDate->format('m') == $endDate->format('m')){
                        $startDay=intval($startDate->format('d'));
                        $endDay=intval($endDate->format('d')); 

                        

                        

                        for($i=$startDay;$i<=$endDay;$i++){
                            $dateInLoop=new \DateTIme($startDateYear.'/'.$startDateMonth.'/'.$i);

                            if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                $strFormat=$dateInLoop->format('Y/m/d');
                                $intFormat=strtotime($strFormat);
                                if(!$this->isNotWorkable($intFormat)){ 
                                  
                                    $grantedAbsences=$grantedAbsences+1;
                                    
                                }
                            }
                        }
                    } 


                    else{
                        $startDayV1=$startDate->format('d');
                        $endDayV1=$startDate->format('t');

                        for($i=$startDayV1;$i<=$endDayV1;$i++){
                            $dateInLoop=new \DateTIme($startDateYear.'/'.$startDateMonth.'/'.$i);

                            if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                $strFormat=$dateInLoop->format('Y/m/d');
                                $intFormat=strtotime($strFormat);
                                if(!$this->isNotWorkable($intFormat)){
                                    $grantedAbsences=$grantedAbsences+1;
                                }
                            }

                        }

                        $startDayV2=1;
                        $endDayV2=$endDate->format('d');

                        for($i=$startDayV2;$i<=$endDayV2;$i++){
                            $dateInLoop=new \DateTime($endDateYear.'/'.$endDateMonth.'/'.$i);


                            if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                $strFormat=$dateInLoop->format('Y/m/d');
                                $intFormat=strtotime($strFormat);
                                if(!$this->isNotWorkable($intFormat)){
                                    $grantedAbsences=$grantedAbsences+1;
                                }
                            }
                        }

                       
                    } 

                    $subVacation->setSvAbsenceDays($grantedAbsences);
                    $em->persist($subVacation);
                }
            

        }

  
    } 

    // si cest pas un congé mais une formation ou une arret maladie (meme logique de traitement mais sans les cas ou l'on peut satuer un mois car durée maximum de 30 jours)
     else{

        foreach($entityDatas as $entityData){
            $startDate=$entityData->getStartDate();
            $endDate=$entityData->getEndDate();

            $startDateMonth=$startDate->format('m');
            $endDateMonth=$endDate->format('m');

            $startDateYear=$startDate->format('Y');
            $endDateYear=$endDate->format('Y');


            if($startDateMonth == $endDateMonth){
                $startDay=intval($startDate->format('d'));
                $endDay=intval($endDate->format('d'));

                $grantedAbsences=0;
                for($i=$startDay;$i<=$endDay;$i++){
                    $dateInLoop= new \DateTime($startDateYear.'/'.$startDateMonth.'/'.$i);
                    if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                        $strFormat=$dateInLoop->format('Y/m/d');
                        $intFormat=strtotime($strFormat);

                        if(!$this->isNotWorkable($intFormat)){
                            $grantedAbsences=$grantedAbsences+1;
                        }
                    }
                }
            } 

            else if($startDateMonth!=$endDateMonth){
                $startDayV1=intval($startDate->format('d'));
                $endDayV1=intval($startDate->format('t'));
                $grantedAbsences=0;
                for($i=$startDayV1;$i<$endDayV1;$i++){
                    $dateInLoop= new \DateTime($startDateYear.'/'.$startDateMonth.'/'.$i);
                    if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                        $strFormat=$dateInLoop->format('Y/m/d');
                        $intFormat=strtotime($strFormat);

                        if(!$this->isNotWorkable($intFormat)){
                            $grantedAbsences=$grantedAbsences+1;
                        }
                    }
                } 

                $startDayV2=1;
                $endDayV2=$endDate->format('d');

                for($i=0;$i<$endDayV2;$i++){
                    $dateInLoop=new \DateTime($endDateYear.'/'.$endDateMonth.'/'.$i);

                    if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                        $strFormat=$dateInLoop->format('Y/m/d');
                        $intFormat=strtotime($strFormat);

                        if(!$this->isNotWorkable($intFormat)){
                            $grantedAbsences=$grantedAbsences+1;
                        }
                    }
                }
            }

            $entityData->setGrantedAbsenceDays($grantedAbsences);
            
            $em->persist($entityData);
            
            

            
        }
    }

    // enfin persist en bdd
        $em->flush();
    } 





    // cette fonction est appliqué lors d'une modification

    public function testConflictsBetweenTypesOfAbsenceModifyAbsence($id,$currentAbsenceSD,$currentAbsenceED,$comparedAbsencesType,$actualAbsence){
        $errorCheck=false;
        $user=$this->getUser();
        $em=$this->getDoctrine()->getManager();
        $now = new \DateTime();
        $absenceRepo=$em->getRepository($comparedAbsencesType);
        $absences=$absenceRepo->findBy(['consultant'=>$id]);
        
        foreach($absences as $index=>$absence){ 
           
          
          // si l'absence n'est pas l'absence actuellement en train d'etre modifié
          if($absence!=$actualAbsence){
             // si la date de debut rentre en conflit avec les dates des autres entités
            if($currentAbsenceSD >= $absence->getStartDate()->getTimeStamp() && $currentAbsenceSD <= $absence->getEndDate()->getTimeStamp()){
                $errorCheck=true;
            } 
            // si la date de fin rentre en conflit avec les dates des autres entités
            if($currentAbsenceED >= $absence->getStartDate()->getTimeStamp() && $currentAbsenceED <= $absence->getEndDate()->getTimeStamp()){
                $errorCheck=true;
            }
        }

      
         
     }

        return $errorCheck;
    }

    // test de conflit lors d'un ajout (donc pas besoin du dernier parametre car l'entité nest pas encore persisté)
    public function testConflictsBetweenTypesOfAbsence($id,$currentAbsenceSD,$currentAbsenceED,$comparedAbsencesType){
        $errorCheck=false;
        $user=$this->getUser();
        $em=$this->getDoctrine()->getManager();
        $now = new \DateTime();
        $absenceRepo=$em->getRepository($comparedAbsencesType);
        $absences=$absenceRepo->findBy(['consultant'=>$id]);
       
        
    
        foreach($absences as $index=>$absence){ 
        
            
            if($currentAbsenceSD >= $absence->getStartDate()->getTimeStamp() && $currentAbsenceSD <= $absence->getEndDate()->getTimeStamp()){
                $errorCheck=true;
            } 

            if($currentAbsenceED >= $absence->getStartDate()->getTimeStamp() && $currentAbsenceED <= $absence->getEndDate()->getTimeStamp()){
                $errorCheck=true;
            }
         
     }
     
       
       
        return $errorCheck;
    } 
    
    

    public function testEnglobingAbsenceRequest($startDate,$endDate, $entityToCompareAgainst){
        $errorCheck=false;

        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();
        // je vise l'entité
        $selectedEntityRepository=$em->getRepository($entityToCompareAgainst);

        $selectedEntityValues=$selectedEntityRepository->findBy(['consultant'=>$user->getId()]);
        
        $englobedAbsenceRequest=[]; 

        if($entityToCompareAgainst == Vacation::class){
            $selectedEntityValues=$selectedEntityRepository->findBy(['consultant'=>$user->getId()]);

           
            // je boucle sur les valeurs des entités

            foreach($selectedEntityValues as $entityValue){

                $entitySubVacations=$entityValue->getSubVacations()->toArray(); 
                // si les valuers de l'entité englobe les valuers d'une autre entité
                if($startDate < $entityValue->getStartDate()->getTimeStamp() && $endDate > $entityValue->getEndDate()->getTimeStamp()){
                    $englobedAbsenceRequest[]=$entityValue;
                }

                // meme logique pour les sous congés
                if(!empty($entitySubVacations)){
                    foreach($entitySubVacations as $subVacation){
                        if($startDate < $subVacation->getStartDate()->getTimeStamp() && $endDate > $subVacation->getEndDate()->getTimeStamp()){
                            $englobedAbsenceRequest[]=$subVacation;
                        }
                    }
                }
            }
    
            
            
        } 

        else{

        foreach($selectedEntityValues as $entityValue){ 
           
            if($startDate < $entityValue->getStartDate()->getTimeStamp() && $endDate > $entityValue->getEndDate()->getTimeStamp()){ 
              

                $englobedAbsenceRequest[]=$entityValue;
            }
        }
    }

        if(!empty($englobedAbsenceRequest)){
            $errorCheck=true;
        } 

       
        return $errorCheck;
    }








}




?>