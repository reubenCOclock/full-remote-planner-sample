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
use App\Form\AccountType;
use App\Entity\MyDocument;
use App\Form\VacationType;
use App\Form\FormationType; 
use App\Entity\PasswordUpdate;
use App\Form\PasswordUpdateType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\FormError;
    

class RHUtilsController extends AbstractController {

    public function calculateAbsentWorkingDays($targetedEntity){
        $em=$this->getDoctrine()->getManager();
        $targetedEntityRepo=$em->getRepository($targetedEntity);
        $entityDatas=$targetedEntityRepo->findAll(); 

        if($targetedEntity==Vacation::class){ 
            
           
            foreach($entityDatas as $entityData){
                $grantedAbsences=0;
                $startDate=$entityData->getStartDate();
                $endDate=$entityData->getEndDate();
    
                $startDateMonth=$startDate->format('m');
                $endDateMonth=$endDate->format('m');
    
                $startDateYear=$startDate->format('Y');
                $endDateYear=$endDate->format('Y');
    
    
                if($startDateMonth == $endDateMonth){
                    $startDay=intval($startDate->format('d'));
                    $endDay=intval($endDate->format('d'));
    
                    
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

                    if($startDateYear == $endDateYear){
                       if($endDateMonth - $startDateMonth >1){
                          
                           for($i=intval($startDateMonth+1);$i<=intval($endDateMonth-1);$i++){
                              $monthDate=new \DateTime($startDateYear.'/'.$i.'/01');
                              
                              for($j=1;$j<=$monthDate->format('t');$j++){
                                  $dateInLoop = new \DateTime($startDateYear.'/'.$i.'/'.$j);
                                  
                                    
                                  if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){ 
                                     
                                    $strFormat=$dateInLoop->format('Y/m/d');
                                    $intFormat=strtotime($strFormat);
            
                                    if(!$this->isNotWorkable($intFormat)){
                                        $grantedAbsences=$grantedAbsences+1;
                                        
                                    }
                                }

                              }


                              $startDayV1=intval($startDate->format('d'));
                              $endDayV1=intval($startDate->format('t'));
                             
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
                              $endDayV2=$endDate->format('d');
              
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

                       else{ 


                        $startDayV1=intval($startDate->format('d'));
                        $endDayV1=intval($startDate->format('t'));
                        
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
                        $endDayV2=$endDate->format('d');
        
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

                    else{
                        if($startDateMonth - $endDateMonth ==11){

                           
                            $startDayV1=intval($startDate->format('d'));
                            $endDayV1=intval($startDate->format('t'));
                            
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
                            $endDayV2=$endDate->format('d');
            
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

                        else{
                            for($i=$startDateMonth+1;$i<=12;$i++){
                                $dateToUse =new \DateTime($startDateYear.'/'.$i.'/01');
                                $daysInDate=$dateToUse->format('t');

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

                            if(intval($endDateMonth>1)){
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


                            $startDayV1=intval($startDate->format('d'));
                            $endDayV1=intval($startDate->format('t'));
                            
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
                    $endDayV2=$endDate->format('d');
    
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
                    /*
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

                    */
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

     else{

        foreach($entityDatas as $entityData){ 

            $grantedAbsences=0;
            $startDate=$entityData->getStartDate();
            $endDate=$entityData->getEndDate();

            $startDateMonth=$startDate->format('m');
            $endDateMonth=$endDate->format('m');

            $startDateYear=$startDate->format('Y');
            $endDateYear=$endDate->format('Y');


            if($startDateMonth == $endDateMonth){
                $startDay=intval($startDate->format('d'));
                $endDay=intval($endDate->format('d'));

                
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




}