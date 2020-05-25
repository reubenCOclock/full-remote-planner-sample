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

   
    class ClientUtilsController extends AbstractController {


        
        public function calculateAbsentWorkingDays($targetedEntity,$entityToModify=null){
            $em=$this->getDoctrine()->getManager();
            $targetedEntityRepo=$em->getRepository($targetedEntity);
            if($entityToModify!=null){
                $entityDatas=[$entityToModify];
            }
            $entityDatas=$targetedEntityRepo->findAll(); 
    
            if($targetedEntity==Vacation::class){ 
                
            
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
                                $dateInLoop=new \DateTime($startDateYear.'/'.$startDateMonth.'/'.$i);
    
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
    
            $em->flush();
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
    
    





     
    }