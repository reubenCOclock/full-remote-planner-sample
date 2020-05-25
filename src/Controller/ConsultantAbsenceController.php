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
   




class ConsultantAbsenceController extends ConsultantUtilsController { 
    

    /**
     * @Route("user/totalAbsences", name="totalAbsences")
     */
   
  public function getTotalConsultantAbsencesPerMonth(){ 
      // j'addtione les absences liés au conges
      $vacationAbsences=$this->getTotalVacationDaysByMonthStartDays()+$this->getTotalVacationDaysByMonthEndDays()+$this->getTotalSubVacationDaysByMonthEnd()+$this->testProlongedVacation();
      // j'addtione les absences liés au arrets maladies
      $sickDayAbsences=$this->getTotalAMDaysStartingMonth()+$this->getTotalAmDaysEndingMonth();
      // j'addtionne les absneces liés au formations
      $formationAbsences=$this->getFormationsByEndDate()+$this->getFormationsByStartDate();
     
     // j'addtione les 3 resultats
      return $vacationAbsences+$sickDayAbsences+$formationAbsences;


  }


     /**
     * @Route("user/getTotalVacationDays",name="getTotalVacationDays")
     */
    
    public function getTotalVacationDaysByMonthStartDays(){ 

        $currentDate=new \DateTIme();
        $currentMonth=$currentDate->format('m');
        $currentYear=$currentDate->format('y');
        $currentMonthIntVal=intval($currentMonth);
        $currentYearIntVal=intval($currentYear);
        $concatenatedYear='20'.$currentYearIntVal;
        $concatenatedIntValYear=intval($concatenatedYear);

        // je recupere les congés avec une date de debut dans le mois
        $targetedVacations=$this->testCongesWithStartDateInMonth();
        
        

        $grantedAbsentDays=0;

        foreach($targetedVacations as $vacation) { 
         //($vacation);
        $subVacations=$vacation->getSubVacations()->toArray();
            // si la date de fin de la congé nest pas dans le mois
          if(intval($vacation->getEndDate()->format('m'))!=$currentMonthIntVal){
             // le dernier jour est le dernier jour du mois
              $lastDayOfMonth=$currentDate->format('t');
              $startDay=intval($vacation->getStartDate()->format('d'));
              $lastDay=intval($lastDayOfMonth); 
              // je boucle sur les dates
              for($i=$startDay;$i<=$lastDay;$i++){
                  $dateInCheck=new \DateTime($concatenatedIntValYear.'/'.$currentMonth.'/'.$i);
                  // si cest pas un wkend
                  if($dateInCheck->format('l')!='Saturday' && $dateInCheck->format('l')!='Sunday'){
                      $strFormat=$dateInCheck->format('Y/m/d');
                      $intFormat=strtotime($strFormat);
                      // si cest pas ferié
                      if(!$this->isNotWorkable($intFormat)){
                        
                          $grantedAbsentDays=$grantedAbsentDays+1;
                      }
                  }
              } 
          } 
          // si la date de debut et la date de fin sont dans le meme mois
         else {
           
            $startDay=intval($vacation->getStartDate()->format('d'));
            $endDay=intval($vacation->getEndDate()->format('d'));
           
            for($i=$startDay;$i<=$endDay;$i++){
                $dateInCheck=new \DateTime($concatenatedIntValYear.'/'.$currentMonth.'/'.$i);
                
                if($dateInCheck->format('l')!='Saturday' && $dateInCheck->format('l')!='Sunday'){
                    $strFormat=$dateInCheck->format('Y/m/d');
                    $intFormat=strtotime($strFormat);
                    if(!$this->isNotWorkable($intFormat)){
                        $grantedAbsentDays=$grantedAbsentDays+1;
                       
                        
                    }
                   
                } 
               
            } 
            
          } 
          
          // je boucle sur les sous congés
          foreach($subVacations as $subVacation){  
              // si la date de fin nest pas dans le mois actuel
            if(intval($subVacation->getEndDate()->format('m'))!=$currentMonthIntVal){
                // si la date de debut est dans le mois actuel 
              if(intval($subVacation->getStartDate()->format('m')==$currentMonthIntVal)){
              
                    // je continue d'incrementer les absences
                     $firstDay=intval($subVacation->getStartDate()->format('d'));
                     $lastDay=intval($currentDate->format('t'));
                     
                     for($i=$firstDay;$i<=$lastDay;$i++){
                         $dateInCheck=new \DateTime($concatenatedIntValYear.'/'.$currentMonth.'/'.$i);
                        
                         if($dateInCheck->format('l')!='Saturday' && $dateInCheck->format('l')!='Sunday'){
                            $strFormat=$dateInCheck->format('Y/m/d');
                            $intFormat=strtotime($strFormat);
            
                            if(!$this->isNotWorkable($intFormat)){
                                $grantedAbsentDays=$grantedAbsentDays+1;
                               
                            }
                         }
                        
                     }
                 }
                       
              } 
              // sinon si la date de debut est la date de fin sont dans le meme mois 
              else{ 

                  
                  $startDay=intval($subVacation->getStartDate()->format('d'));
                  $endDay=intval($subVacation->getEndDate()->format('d'));
                  for($i=$startDay;$i<=$endDay;$i++){
                      $dateInCheck=new \DateTime($concatenatedIntValYear.'/'.$currentMonth.'/'.$i);

                      if($dateInCheck->format('l')!='Saturday' && $dateInCheck->format('l')!='Sunday'){ 
                          $strFormat=$dateInCheck->format('Y/m/d');
                          $intFormat=strtotime($strFormat);
                          if(!$this->isNotWorkable($intFormat)){
                              $grantedAbsentDays=$grantedAbsentDays+1;
                          }

                      }
                  }

              }
          }
         

            
        } 
        
      
        return $grantedAbsentDays;
    } 




    /**
     * @Route("user/testEndDays",name="testEndDays")
     */

    public function getTotalVacationDaysByMonthEndDays(){
       
       

        $currentDate=new \DateTIme();
        $currentMonth=$currentDate->format('m');
        $currentYear=$currentDate->format('y');
        $currentMonthIntVal=intval($currentMonth);
        $currentYearIntVal=intval($currentYear);
        $concatenatedYear='20'.$currentYearIntVal;
        $concatenatedIntValYear=intval($concatenatedYear);
        // je recupere les congés avec une date de de fin dans le mois
        $targetedVacations=$this->testCongesWithEndDateInMonth();
       
        
        $grantedAbsenceDays=0;
        foreach($targetedVacations as $vacation){
            $endDay=intval($vacation->getEndDate()->format('d'));
            // etant donée que la congé commence dans un autre mois, ca date de debut et le 1er du mois
            $firstDay=1;
            // je boucle sur les dates
            for($i=$firstDay;$i<=$endDay;$i++){
                $dateInCheck=new \DateTime($concatenatedIntValYear.'/'.$currentMonth.'/'.$i);
                // si c'est pas un wkend
                if($dateInCheck->format('l')!='Saturday' && $dateInCheck->format('l')!='Sunday'){
                    $strFormat=$dateInCheck->format('Y/m/d');
                    $intFormat=strtotime($strFormat);
                    // si cest pas ferié
                    if(!$this->isNotWorkable($intFormat)){
                        //j'incremente les absences par 1
                        $grantedAbsenceDays=$grantedAbsenceDays+1;
                    }
                }
                
            }

        } 
       
       
       return $grantedAbsenceDays;

    } 




      /**
     * @Route("user/subVacationEndDays", name="subVacationEndDays")
     */

    public function getTotalSubVacationDaysByMonthEnd(){
        $currentDate=new \DateTIme();
        $currentMonth=$currentDate->format('m');
        $currentYear=$currentDate->format('y');
        $currentMonthIntVal=intval($currentMonth);
        $currentYearIntVal=intval($currentYear);
        $concatenatedYear='20'.$currentYearIntVal;
        $concatenatedIntValYear=intval($concatenatedYear);

        // je recupere les sous congés qui ont une date de debut ou date de fin dans le mois

        $subVacationsWithEndDays=$this->testSubCongesWithEndDateInMonth();
        
        $grantedAbsenceDays=0;

        foreach($subVacationsWithEndDays as $subVacation){
            
            $firstDay=intval($subVacation->getStartDate()->format('d'));
            $endDay=intval($subVacation->getEndDate()->format('d'));
            // si la sous congé ne commence pas dans le mois
            if($subVacation->getStartDate()->format('m')!=$currentMonth){
                $firstDay=1;
            }
            // si la sous congé ne se termine pas dans le mois
            if($subVacation->getEndDate()->format('m')!=$currentMonth){
                $endDay=intval($currentDate->format('t'));
            } 

          

            // je boucle sur toutes les dates contenus dans la sous congé
            for($i=$firstDay;$i<=$endDay;$i++){
               
                $dateInCheck=new \DateTime($concatenatedIntValYear.'/'.$currentMonth.'/'.$i);
                // si cest pas un wkend
                if($dateInCheck->format('l')!='Saturday' && $dateInCheck->format('l')!='Sunday'){
                    $strFormat=$dateInCheck->format('Y/m/d');
                    $intFormat=strtotime($strFormat);
                    if(!$this->isNotWorkable($intFormat)){
                        // si cest pas ferié j'incremente les absences pas 1 
                        $grantedAbsenceDays=$grantedAbsenceDays+1;
                        
                    }
                }
                
            }
        }
       
        
       
        return $grantedAbsenceDays;
    }




    

    /**
     * @Route("user/testSubVacations", name="testSubVacations")
     */
    
    

    public function testSubCongesWithEndDateInMonth(){
        $user=$this->getUser();
        $em=$this->getDoctrine()->getManager();
        $now=new \DateTIme();
        $currentMonth=$now->format('m');
        $currentYear=$now->format('y');
        $vacationRepo=$em->getRepository(Vacation::class);
        // je recupere les congés
        $vacations=$vacationRepo->findBy(['consultant'=>$user->getId(),'isValidated'=>true]);
        $subVacationsWithEndDatesInDifferentMonth=[];
        foreach($vacations as $vacation){
            // je recupere les sous congés
            $subVacations=$vacation->getSubVacations()->toArray();
            foreach($subVacations as $subVacation){
               // si la sous congé a une date de fin dans le mois
                if($subVacation->getEndDate()->format('m')==$currentMonth){
                    if($subVacation->getStartDate()->format('m')!=$currentMonth){ 
                        // si la date de debut n'est pas dans le mois, j'ailemente le tableau
                        $subVacationsWithEndDatesInDifferentMonth[]=$subVacation;
                    }
                } 
                // si la congé n'a pas la date de debut dans le mois
                if($vacation->getStartDate()->format('m')!=$currentMonth){ 
                    //si la sous congé a une date de debut dans le mois
                    if($subVacation->getStartDate()->format('m')==$currentMonth){ 
                        // j'alimente le tableau
                       $subVacationsWithEndDatesInDifferentMonth[]=$subVacation;
                   
                    }
                }
            } 
        } 
       
      
       
        return $subVacationsWithEndDatesInDifferentMonth;
    } 




    /**
     * @Route("user/testCongeEndDate", name="testCongeEndDate")
     */
    
    public function testCongesWithEndDateInMonth(){
        $user=$this->getUser();
        $em=$this->getDoctrine()->getManager();
        $now=new \DateTIme();
        $currentMonth=$now->format('m');
        $currentYear=$now->format('y');
        $vacationRepo=$em->getRepository(Vacation::class); 
        // je recupere les congés qui correspondent a l'utilisateur
        $userVacations=$vacationRepo->findBy(['consultant'=>$user->getId(),'isValidated'=>true]);
       
        $endDatesInMonth=[];
        // je boucle sur les congés
        foreach($userVacations as $vacation){ 
           // si la congé a une date de fin dans le mois actuel, le tableau s'alimente
            if($vacation->getEndDate()->format('m')==$currentMonth && $vacation->getEndDate()->format('y')==$currentYear){
                if($vacation->getStartDate()->format('m')!=$currentMonth){
                    $endDatesInMonth[]=$vacation;
                }
            }
        } 

    
        return $endDatesInMonth;
        
    }



      
    /**
     * @Route("user/testConge", name="testConge")
     */

    public function testCongesWithStartDateInMonth(){ 
        $user=$this->getUser();
        $em=$this->getDoctrine()->getManager();
        $now=new \DateTIme();
        $currentMonth=$now->format('m');
        $currentYear=$now->format('y');

        $vacationRepo=$em->getRepository(Vacation::class);
        // je recupere les congés de l'utilsateur en cours 
        $userVacations=$vacationRepo->findBy(['consultant'=>$user->getId(),'isValidated'=>true]);
        
        $startDatesInMonth=[];
        foreach($userVacations as $vacation){ 
            // si la congé commence dans le mois actuel le tableau s'alimente
           if($vacation->getStartDate()->format('m')==$currentMonth && $vacation->getStartDate()->format('y')==$currentYear){
               $startDatesInMonth[]=$vacation;
           }
        } 
        
        return $startDatesInMonth;
        
  
    } 

    /**
     * @Route("user/prolongedVacation")
     */
    
    public function testProlongedVacation(){
        $now=new \DateTIme();
        $user=$this->getUser();
        $currentMonth=$now->format('m');
        $currentYear=$now->format('y');
        $em=$this->getDoctrine()->getManager();
        $vacationRepo=$em->getRepository(Vacation::class);
        // je recupere les vaccances qui correspondent au consultant
        $userVacations=$vacationRepo->findBy(['consultant'=>$user->getId(),'isValidated'=>true]);
        // vaccances initlialisé a zero
        $grantedAbsenceDays=0;
        foreach($userVacations as $vacation){
            $startDateMonth=$vacation->getStartDate()->format('m');
            $subVacations=$vacation->getSubVacations()->toArray();
            // si il y a des sous congés
            if(!empty($subVacations)){
                foreach($subVacations as $subVacation){ 
                    $startDateMonthSV=$subVacation->getStartDate()->format('m');
                    $endDateMonthSV=$subVacation->getEndDate()->format('m');
                    // si on est pas dans le mois ou il y a une congé ou sous congé qui commence ni dans le mois avec une fin de sous congé
                    if($now->format('m')!=$startDateMonth && $now->format('m')!=$startDateMonthSV && $now->format('m')!=$endDateMonthSV){ 
                        // si on est entre le debut est la fin de la sous congé
                        if($now>$subVacation->getStartDate() && $now<$vacation->getEndDate()){ 
                            // chaque jour ouvré est consideré comme une absence
                            $grantedAbsenceDays=$this->getCurrentMonthAndWorkingDays()['workingDays'];
                            
                        }
                    }

                   
                }
            } 

           
                $endDateMonth=$vacation->getEndDate()->format('m');
                // si on est ni dans un mois avec une date de fin ni dans un mois avec une date de debut
                if($now->format('m')!=$startDateMonth && $now->format('m')!=$endDateMonth){ 
                    // si on est entre la date de debut est la de de find
                    if($now>$vacation->getStartDate() && $now< $vacation->getEndDate()){ 
                        // chaque jour ouvré est consideré comme une absence
                        $grantedAbsenceDays=$this->getCurrentMonthAndWorkingDays()['workingDays'];
                    }
                }
            
        }
        return $grantedAbsenceDays;
       
    }


  
     
   

    /**
     * @Route("user/totalAMDaysEndingMonth", name="totalAmDaysEndingMonth")
     */
    
     // meme logique que la fonction getFormationsByEndDate
    public function getTotalAmDaysEndingMonth(){ 
        $now=new \DateTIme();
        $currentYear=$now->format('Y');
        $currentMonth=$now->format('m');
        $amDaysEndingInMonth=$this->getTypeByEndDate(SickDay::class);
        $grantedAbsenceDays=0;
      
        foreach($amDaysEndingInMonth as $am){
            $startDay=1;
            $endDay=$am->getEndDate()->format('d');
            for($i=$startDay;$i<=$endDay;$i++){
                $dateInLoop=new \DateTime($currentYear.'/'.$currentMonth.'/'.$i);
                if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                    $strFormat=$dateInLoop->format('Y/m/d');
                    $intFormat=strtotime($strFormat);
                    if(!$this->isNotWorkable($intFormat)){
                        $grantedAbsenceDays=$grantedAbsenceDays+1;
                    }
                }
               
                
            }
        } 
        return $grantedAbsenceDays;  
    } 

    /**
     * @Route("user/totalAMDaysStartingMonth",name="totalAMDaysStartingMonth")
     */

    // meme logique que la fonction getFormationsByStartDate
    public function getTotalAMDaysStartingMonth(){
        $now=new \DateTIme();
        $currentYear=$now->format('Y');
        $currentMonth=$now->format('m');
        $amDaysStartingInMonth=$this->getTypeByStartDate(SickDay::class);
        $grantedAbsenceDays=0;
        
        foreach($amDaysStartingInMonth as $amDay){
            $startDay=$amDay->getStartDate()->format('d');
            $endDay=$amDay->getEndDate()->format('d');
            
            if($amDay->getEndDate()->format('m')!=$currentMonth){
                $endDay=$now->format('t');
            }

            for($i=$startDay;$i<=$endDay;$i++){
                $dateInLoop=new \DateTime($currentYear.'/'.$currentMonth.'/'.$i);
                if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                    $strFormat=$dateInLoop->format('Y/m/d');
                    $intFormat=strtotime($strFormat);
                    if(!$this->isNotWorkable($intFormat)){
                        $grantedAbsenceDays=$grantedAbsenceDays+1;
                    }
                }
            }
            
        }
       
        return $grantedAbsenceDays;
    } 
    
    /**
     * @Route("user/endDateFormation", name="endDateFormation")
     */

    public function getFormationsByEndDate (){
        $now=new \DateTIme();
        $currentYear=$now->format('Y');
        $currentMonth=$now->format('m');
        // je recupere les formation qui on une date de fin qui correspondent au mois et a l'anee
        $formationsInEndDate=$this->getTypeByEndDate(Formation::class);
       
        $grantedAbsenceDays=0; 
        // je boucle sur ce tableau
        foreach($formationsInEndDate as $formation){ 
            //dans ce cas la, la formation a forcement une date de debut qui n'est pas dans le meme mois dans la date de debut est initialisé au 1er du mois
            $startDate=1;
            $endDate=$formation->getEndDate()->format('d');
            // je boucle sur les dates de la formation
            for($i=$startDate;$i<=$endDate;$i++){
                $dateInLoop=new \DateTime($currentYear.'/'.$currentMonth.'/'.$i);
                // si la date ne correspond pas au wk end
                if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                    $strFormat=$dateInLoop->format('Y/m/d');
                    $intFormat=strtotime($strFormat);
                    if(!$this->isNotWorkable($intFormat)){
                         // si cest pas une jour ferier
                        $grantedAbsenceDays=$grantedAbsenceDays+1;
                    }
                }
            }
        }
       return $grantedAbsenceDays;
    } 

    /**
     * @Route("user/getStartDateFormation",name="getStartDate")
     */
    public function getFormationsByStartDate(){
        $now=new \DateTIme();
        $currentYear=$now->format('Y');
        $currentMonth=$now->format('m');
        // recuperation des formations qui ont une date de debut dans le mois et dans l'anee
        $formationsInStartDate=$this->getTypeByStartDate(Formation::class);
        // jours d'absences initialisés a 0
        $grantedAbsenceDays=0;
        // je boucle sur ce tableau de formations
        foreach($formationsInStartDate as $formation){
            
            $startDate=$formation->getStartDate()->format('d');
            $endDate=$formation->getEndDate()->format('d'); 
            // si la date de fin n'est pas dans le meme mois que la date de debut
            if($formation->getEndDate()->format('m')!=$currentMonth){
                // la date de fin est le dernier jours du mois 
                $endDate=$now->format('t');
            }

            // je boucle sur chaque date (la date de debut a la date de fin)
            for($i=$startDate;$i<=$endDate;$i++){
                
                $dateInLoop=new \DateTime($currentYear.'/'.$currentMonth.'/'.$i);
                // si la date ne correspond pas au wk end
                if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                    $strFormat=$dateInLoop->format('Y/m/d');
                    $intFormat=strtotime($strFormat); 
                    // si cest pas une jour ferier
                    if(!$this->isNotWorkable($intFormat)){
                        // les absences augmentent pas 1
                        $grantedAbsenceDays=$grantedAbsenceDays+1;
                    }
                }
            }
        }

        return $grantedAbsenceDays;
    }

    public function getTypeByStartDate($typeOfConge) {
        $currentDate=new \DateTIme();
        $currentYear=$currentDate->format('Y');
        $currentMonth=$currentDate->format('m');
        $user=$this->getUser();
        $em=$this->getDoctrine()->getManager(); 
        // je recupere le parametre (de type formation ou de type arret maladie)
        $typeRepo=$em->getRepository($typeOfConge); 
        if($typeOfConge==Formation::class){
            $typesOfCongeDaysByUser=$typeRepo->findBy(['consultant'=>$user->getId(),'isValidated'=>true]);
        } 
        else{
        $typesOfCongeDaysByUser=$typeRepo->findBy(['consultant'=>$user->getId()]);
        }
        $typeOfCongeStartingInMonth=[];
        foreach($typesOfCongeDaysByUser as $type){ 
          // a chaque tour de boucle si la formation ou l'arret maladie a une date de debut qui correspond au mois ou a l'anee, je rajoute dans le tableau
            if($type->getStartDate()->format('m')==$currentMonth && $type->getStartDate()->format('Y')==$currentYear) {
                $typeOfCongeStartingInMonth[]=$type;
            }
        } 
       return $typeOfCongeStartingInMonth;
        
    } 
    /**
     * @Route("user/testSickDaysEndingInMonth", name="testSickDaysEndingInMonth")
     */
    
    public function getTypeByEndDate($typeOfConge){
        $currentDate=new \DateTIme();
        $currentYear=$currentDate->format('Y');
        $currentMonth=$currentDate->format('m');
        $user=$this->getUser();
        $em=$this->getDoctrine()->getManager();
        // je recupere le parametre (de type formation ou de type arret maladie)
        $typeRepo=$em->getRepository($typeOfConge); 
        if($typeOfConge==Formation::class){
            $typesOfCongeDaysByUser=$typeRepo->findBy(['consultant'=>$user->getId(),'isValidated'=>true]);
        } 
        else{
            $typesOfCongeDaysByUser=$typeRepo->findBy(['consultant'=>$user->getId()]);
        }
        $typeOfCongeEndingInMonth=[];
        // je boucle sur cette entité
        foreach($typesOfCongeDaysByUser as $type){ 
        // a chaque tour de boucle,ce la date de fin correspond au mois et l'anee, j'ajoute dans le tableau
        if($type->getEndDate()->format('m')==$currentMonth && $type->getEndDate()->format('Y')==$currentYear){
            if($type->getStartDate()->format('m')!=$currentMonth){
                $typeOfCongeEndingInMonth[]=$type;
            }
        }
     } 
     return $typeOfCongeEndingInMonth;
    }





 
}










?>