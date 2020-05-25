<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Role;
use App\Entity\Vacation;
use App\Entity\Formation;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Utils\Slugger;
use App\Form\VacationType;
use App\Form\FormationType;
use App\Form\ProjectType;
use App\Form\MonthlySummaryType;
use App\Entity\Project;
use App\Entity\MonthlySummary;
use App\Entity\SickDay;
use App\Form\SickDayType;
use App\Form\UserType;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\MyDocument;
use App\Form\MyDocumentType;
use App\Entity\Feedback;
use App\Form\FeedbackType;


class FormController extends AbstractController {  
   /**
    * @Route("/myDocumentTest",name="myDocumentTest")
    */

    public function myDocumentTest(Request $request){
       $em=$this->getDoctrine()->getManager();
       $document=new MyDocument();
       $form=$this->createForm(MyDocumentType::class,$document);
       $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid()){ 
          
         $file=$form['url']->getData(); 

         $fileName = md5(uniqid()).'.'.$file->guessExtension();
         // Move the file to the directory where brochures are stored
         $file->move(
             $this->getParameter('doc_directory'),
             $fileName
         );
         
         // Update the 'brochure' property to store the PDF file name
         // instead of its contents
         $document->setUrl($fileName);
         
         $em->persist($document);
         $em->flush();
         return $this->render('forms/doc_test.html.twig',['document'=>$document]);
       } 

       return $this->render('forms/document_form.html.twig',['form'=>$form->createView()]);

    }
   /**
    * @Route("/userFormTest", name="userFormTest")
    */

    public function userFormTest(Request $request){
      $em=$this->getDoctrine()->getManager();
      $user=new User();
      $role=new Role();
      $role->setRoleTitle('ROLE_CONSULTANT');
      $role->setDescription('the description of a consultant');
      $em->persist($role);
      $ssIdnumberRange=range(1,30);
      shuffle($ssIdnumberRange);
      $ssRandomNumberString=implode($ssIdnumberRange);
      $ssRandomNumberInt=intval($ssRandomNumberString);
      
      $user->setSsId($ssRandomNumberInt);
      $user->setRole($role);
   
      $form=$this->createForm(UserType::class,$user);
      $form->handleRequest($request);
      
      if($form->isSubmitted() && $form->isValid()){
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
         $em->persist($user);
         $em->flush();
         return $this->render('forms/user_test.html.twig',['user'=>$user]);
         
      } 
      return $this->render('forms/user_form.html.twig',['form'=>$form->createView()]);
    }
   /**
    * @Route("/sickDayFormTest", name="sickDayFormTest")
    */

   public function sickDayFormTest(Request $request){
      $em=$this->getDoctrine()->getManager();
      $sickDay= new SickDay();
      $myDocument=new MyDocument();
      
      $form=$this->createForm(SickDayType::class, $sickDay);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid()){
         $em->persist($sickDay);
         $em->flush();
         return $this->redirectToRoute('formSuccess');
      } 

      return $this->render('forms/sick_day.html.twig',['form'=>$form->createView()]);
   }


   /**
    * @Route("/monthlySummaryFormTest",name="monthlySummaryFormTest")
    */

   public function testMonthlySummaryForm(Request $request){
      $em=$this->getDoctrine()->getManager();
      $monthlySummary=new MonthlySummary();
      $form=$this->createForm(MonthlySummaryType::class,$monthlySummary);
      $form->handleRequest($request);
      if($form->isSubmitted() && $form->isValid()){ 
        
         $em->persist($monthlySummary);
         $em->flush();
         return $this->redirectToRoute('formSuccess');
         
      }

      return $this->render('forms/monthly_summary.html.twig',['form'=>$form->createView()]);
      
   }
    /**
     * @Route("/vacationFormTest",name="vacationFormTest")
     * 
     */ 
    

      public function testVacationForm(Request $request){ 
       $em=$$this->getDoctrine()->getManager();
       $vacation =new Vacation();
        $form=$this->createForm(VacationType::class,$vacation);
        
        $form->handleRequest($request); 
        if ($form->isSubmitted() && $form->isValid()){ 
           
            $em->persist($vacation);
            $em->flush(); 
            return $this->redirectToRoute('formSuccess');
        }

        
        return $this->render('forms/vacation_form.html.twig',['form'=>$form->createView()]);
     } 
 
     /**
      * @Route("/formationFormTest", name="formationFormTest")
      */
     public function testFormationForm(Request $request){ 
        $em=$this->getDoctrine()->getManager();
        $formation =new Formation();
        $form=$this->createForm(FormationType::class, $formation);
        $form->handleRequest($request); 
        if($form->isSubmitted() && $form->isValid()){
            $em->persist($formation);
            $em->flush();
            return $this->redirectToRoute('formSuccess');
            
        }
        return $this->render('forms/formation_form.html.twig',['form'=>$form->createView()]);

     } 

     /**
      * @Route("/projectFormTest",name="projectFormTest")
      */ 

      public function testProjectForm(Request $request){
         $em=$this->getDoctrine()->getManager();
        $project=new Project();
         
        $form=$this->createForm(ProjectType::class,$project);
        $form->handleRequest($request); 
        if($form->isSubmitted() && $form->isValid()){
           $em->persist($project);
            $em->flush();
            return $this->redirectToRoute('formSuccess');
        }
        return $this->render('forms/project_form.html.twig',['form'=>$form->createView()]);
      }  
      /**
       * @Route("/feedbackFormTest", name="feedbackFormTest")
       */

      public function testFeedbackForm(Request $request){
         $em=$this->getDoctrine()->getManager();
         $feedback=new Feedback();
         $form=$this->createForm(FeedbackType::class,$feedback);
         $form->handleRequest($request);
         if($form->isSubmitted() && $form->isValid()){
            dd($feedback);
         } 
      
         return $this->render('forms/feedback_test.html.twig',['form'=>$form->createView()]);
         
      }

      /**
       * @Route("/formSuccess", name="formSuccess")
       */

      public function formSucess(){
         return $this->render('forms/success_page.html.twig');
      }
 
}