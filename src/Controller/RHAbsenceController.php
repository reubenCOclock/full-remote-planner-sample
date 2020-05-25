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
use App\Form\ChooseAbsenceDetailsType;
use App\Entity\PasswordUpdate;
use App\Form\PasswordUpdateType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\FormError;


class RHAbsenceController extends RHUtilsController {

    /**
     * @Route("/rh/{slug}/filterAbsenceView/{sconsultantId}/{year}", name="filterAbsenceView")
     */
    public function sortAbsencesByConsultant($entityClass,$consultantId){
        $em=$this->getDoctrine()->getManager();

        $absenceEntityRepo=$em->getRepository($entityClass);

        
        $absenceEntityValues=$absenceEntityRepo->sortAbsencesByUserAndEndDate($consultantId);

       

       return $absenceEntityValues;
    

    } 

 /*
    public function getOverLappingAbsences($entity,$consultantId,$year){

        $em=$this->getDoctrine()->getManager();

        $repo=$em->getRepository($entity);

        $absencesArray=$repo->findBy(['consultant'=>$consultantId]);
        if($entity==Vacation::class){
            $overlappingAbsences=[];
            foreach($absencesArray as $absence){
                $absencesSV=$absence->getSubVacations()->toArray();
                if($absence->getStartDate()->format('Y')==$year && $absence->getEndDate()->format('Y')==$year) {
                
                    if(!empty($absencesSV)){
                        foreach($absencesSV as $absenceSV){
                            if($absenceSV->getEndDate()->format('Y')==$year && $absenceSV->getStartDate()->format('Y')!=$year){
                                $overlappingAbsences[]=$absence;
                            }

                            if($absenceSV->getStartDate()->format('Y')==$year && $absenceSV->getEndDate()->format('Y')!=$year){
                                $overlappingAbsences[]=$absence;
                            }
                        }
                    } 


                } 

                else if($absence->getStartDate()->format('Y')==$year && $absence->getEndDate()->format('Y')!=$year){
                    $overlappingAbsences[]=$absence;
                }

                else if($absence->getStartDate()->format('Y')!=$absence->getEndDate()->format('Y')){
                    $overlappingAbsences[]=$absence;
                }
            }
        }

        else{
            foreach($absencesArray as $absence){
                if($absence->getStartDate()->format('Y')==$year && $absence->getEndDate()->format('Y')!=$year){
                    $overlappingAbsences[]=$absence;
                }

                else if($absence->getStartDate()->format('Y')!=$absence->getEndDate()->format('Y')){
                    $overlappingAbsences[]=$absence;
                }
            }
        }

        return $overlappingAbsences;
    }

    public function filterAbsenceByYear($entity,$consultantId,$year) {
        $em=$this->getDoctrine()->getManager();
        $repo=$em->getRepository($entity);

        if($entity==Vacation::class){

        $absences=$repo->findBy(['consultant'=>$consultantId]);

        $absenceValueInCurrentYear=[];

        foreach($absences as $absence){
            $absenceDaysInMonth=0;
            if($absence->getStartDate()->format('Y')==$year && $absence->getEndDate()->format('Y')!=$year) {
                    $firstDate=$absence->getStartDate();

                    $lastDate=new \DateTime($year.'/12/31');

                    $currentMonth=$firstDate->format('m');

                   

                    if($lastDate->format('m') == $firstDate->format('m')){
                        $firstDay=$firstDate->format('d');
                        $lastDay=$lastDate->format('d');

                        for($i=$firstDay;$i<=$lastDay;$i++){
                            $dateInLoop=new \DateTime($year.'/'.$currentMonth.'/'.$i);

                             if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                               $strFormat=$dateInLoop->format('Y/m/d');
                               $intFormat=strtotime($strFormat);
                               if(!$this->isNotWorkable($intFormat)){
                                   $absenceDaysInMonth=$absenceDaysInMonth+1;
                                   
                               }
                            }
                        }
                        $absenceValueInCurrentYear[]=$absenceDaysInMonth;
                    }

                    else{
                        $absenceDaysInMonth='supperieur a 30';
                        $absenceValueInCurrentYear[]=$absenceDaysInMonth;
                    }
                
            } 

            if($absence->getEndDate()->format('Y') == $year && $absence->getStartDate()->format('Y')!=$year){
                $firstDate=new \DateTime($year.'/01/01');

                $lastDate=$absence->getEndDate();

                $currentMonth=$firstDate->format('m');

               

                if($lastDate->format('m') == $firstDate->format('m')){
                    $firstDay=$firstDate->format('d');
                    $lastDay=$lastDate->format('d');

                    for($i=$firstDay;$i<=$lastDay;$i++){
                        $dateInLoop=new \DateTime($year.'/'.$currentMonth.'/'.$i);

                         if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                           $strFormat=$dateInLoop->format('Y/m/d');
                           $intFormat=strtotime($strFormat);
                           if(!$this->isNotWorkable($intFormat)){
                               $absenceDaysInMonth=$absenceDaysInMonth+1;
                               
                           }
                        }
                    }
                    $absenceValueInCurrentYear[]=$absenceDaysInMonth;
                }

                else {
                    if($lastDate->format('m')-$firstDate->format('m') > 1){ 
                        $firstMonth=intval($firstDate->format('m'));
                        $lastMonth=intval($lastDate->format('m'));
                        for($i=$firstMonth+1;$i<=$lastMonth-1;$i++){
                            $dateReference=new \DateTime($lastDate->format('Y').'/'.$i.'/01');
                            for($j=0;$j<=$dateReference->format('t');$j++){
                                $dateInLoop=new \DateTime($lastDate->format('Y').'/'.$i.'/'.$j);
                                if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                    $strFormat=$dateInLoop->format('Y/m/d');
                                    $intFormat=strtotime($strFormat);
                                    if(!$this->isNotWorable($intFormat)){
                                        $absenceDaysInMonth=$absenceDaysInMonth+1;
                                    }
                                }
                            }
                        } 

                        $lastDay=$lastDate->format('d');

                        for($i=1;$i<$lastDay;$i++){
                            $dateInLoop=new \DateTime($lastDate->format('Y').'/'.$lastDate->format('m').'/'.$i);
                            if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                $strFormat=$dateInLoop->format('Y/m/d');
                                $intFormat=strtotime($strFormat);
                                if(!$this->isNotWorable($intFormat)){
                                    $absenceDaysInMonth=$absenceDaysInMonth+1;
                                }
                            }
                        }


                    } 
                    
                    else{

                    }

                   

                    
                }
            }

            if($absence->getEndDate()->format('Y')==$year && $absence->getStartDate()->format('Y')==$year){
                $absencesSV=$absence->getSubVacations()->toArray();

                foreach($absencesSV as $absenceSV){
                    if($absenceSV->getStartDate()->format('Y') == $year && $absenceSV->getEndDate()->format('Y')!=$year){ 
                        $firstDate=$absenceSV->getStartDate();


                        $lastDate=new \DateTime($year.'/12/31');

                        
                        $currentMonth=$firstDate->format('m');
    
                        $absenceDaysInMonth=0;
    
                        if($lastDate->format('m') == $firstDate->format('m')){
                            $firstDay=$firstDate->format('d');
                            $lastDay=$lastDate->format('d');
    
                            for($i=$firstDay;$i<=$lastDay;$i++){
                                $dateInLoop=new \DateTime($year.'/'.$currentMont.'/'.$i);
    
                                 if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                   $strFormat=$dateInLoop('Y/m/d');
                                   $intFormat=strtotime($strFormat);
                                   if(!$this->isNotWorkable($intFormat)){
                                       $absenceDaysInMonth=$absenceDaysInMonth+1;
                                       
                                   }
                                }
                            }

                            $absenceValueInCurrentYear[]=$absenceDaysInMonth;
                        }
    
                        else{
                            $absenceDaysInMonth='supperieur a 30';
                            $absenceValueInCurrentYear[]=$absenceDaysInMonth;
                        }
                    }

                    if($absenceSV->getEndDate()->format('Y') == $year && $absenceSV->getStartDate()->format('Y')!=$year) {
                        

                        $firstDate=new \DateTime($year.'/12/31');

                        $firstDate=$absenceSV->getEndDate();
    
                        $currentMonth=$firstDate->format('m');
    
                        $absenceDaysInMonth=0;
    
                        if($lastDate->format('m') == $firstDate->format('m')){
                            $firstDay=$firstDate->format('d');
                            $lastDay=$lastDate->format('d');
    
                            for($i=$firstDay;$i<=$lastDay;$i++){
                                $dateInLoop=new \DateTime($year.'/'.$currentMont.'/'.$i);
    
                                 if($dateInLoop->format('l')!='Saturday' && $dateInLoop->format('l')!='Sunday'){
                                   $strFormat=$dateInLoop('Y/m/d');
                                   $intFormat=strtotime($strFormat);
                                   if(!$this->isNotWorkable($intFormat)){
                                       $absenceDaysInMonth=$absenceDaysInMonth+1;
                                      
                                   }
                                }
                            }

                            $absenceValueInCurrentYear[]=$absenceDaysInMonth;
                        }
    
                        else{
                            $absenceDaysInMonth='supperieur a 30';
                            $absenceValueInCurrentYear[]=$absenceDaysInMonth;
                        }
                    }
                }
            }

            
        }

        return $absenceValueInCurrentYear;
    } 

    

}
*/

}









?>