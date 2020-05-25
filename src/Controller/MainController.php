<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\ForgotPassword;
use App\Form\ForgotPasswordType;
use Symfony\Component\HttpFoundation\Request;



class MainController extends AbstractController {
    /**
     * @Route("/", name="main")
     */
    public function index(UserPasswordEncoderInterface $hasher)
    { 
        $em=$this->getDoctrine()->getManager();
        
        $userRepo=$this->getDoctrine()->getManager()->getRepository(User::class);
        $users=$userRepo->findAll();
    
        return $this->render('home_page.html.twig', [
            'controller_name' => 'TestController ',
        ]);
    } 
     
    /**
     * @Route("user/{slug}", name="userpage")
     */

    public function userPage($slug){ 
        if(!$this->getUser()->getIsEmployed()){
            throw new \Exception('you are no longer an employee');
        }
        return $this->render('users/user.html.twig');
    }  

        /**
     * @Route("client/{slug}", name="clientpage")
     */

    public function clientPage($slug){ 
        if(!$this->getUser()->getIsEmployed()){
            throw new \Exception('you are no longer an employee');
        }
        return $this->render('users/user.html.twig');
    } 

        /**
     * @Route("rh/{slug}", name="rhpage")
     */

    public function rhPage($slug){ 
        if(!$this->getUser()->getIsEmployed()){
            throw new \Exception('you are no longer an employee');
        }
        return $this->render('users/user.html.twig');
    } 

    /**
     * @Route("admin/{slug}", name="adminpage")
     */
    public function adminPage($slug){ 
        if(!$this->getUser()->getIsEmployed()){
            throw new \Exception('you are no longer an employee');
        }
        return $this->render('users/user.html.twig');
    } 
    
    /**
     * @Route("rh/{slug}/team", name="trombinoscope_rh")
     */
    public function vblTeamTrombiRh($slug){ 
        $em=$this->getDoctrine()->getManager();
        $userRepo=$em->getRepository(User::class);
        $members=$userRepo->findAll();
    
        return $this->render('users/trombinoscope.html.twig', ['users'=> $members]);
        //return new Response('hello world');
    } 
    
    /**
     * @Route("user/{slug}/team", name="trombinoscope_user")
     */
    public function vblTeamTrombiUser($slug){ 
        $em=$this->getDoctrine()->getManager();
        $userRepo=$em->getRepository(User::class);
        $members=$userRepo->findAll();
    
        return $this->render('users/trombinoscope.html.twig', ['users'=> $members]);
        //return new Response('hello world');
    } 
    /**
     * @Route("admin/{slug}/team", name="trombinoscope_admin")
     */
    public function vblTeamTrombiAdmin($slug){ 
        $em=$this->getDoctrine()->getManager();
        $userRepo=$em->getRepository(User::class);
        $members=$userRepo->findAll();

        return $this->render('users/trombinoscope.html.twig', ['users'=> $members]);
        //return new Response('hello world');
    } 

        /**
     * @Route("client/{slug}/team", name="trombinoscope_client")
     */
    public function vblTeamTrombiClient($slug){ 
        $em=$this->getDoctrine()->getManager();
        $userRepo=$em->getRepository(User::class);
        $members=$userRepo->findAll();

        return $this->render('users/trombinoscope.html.twig', ['users'=> $members]);
        //return new Response('hello world');
    } 
    
     /**
     * @Route("forgotPassword", name="forgotPassword")
     */
    public function forgotPasswordPage(Request $request,\Swift_Mailer $mailer,UserPasswordEncoderInterface $hasher){ 
        $forgotPassword=new ForgotPassword();
        $form=$this->createForm(ForgotPasswordType::class,$forgotPassword);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em=$this->getDoctrine()->getManager();
            $users=$em->getRepository(User::class);
            $typedEmail=$forgotPassword->getEmail(); 
            $user=$users->findBy(['email'=>$typedEmail]);
            if($user){ 
                $userEmail=$user[0]->getEmail();
               $randomMDP=$this->getRandomString(10);
               $message=(new \Swift_Message('Votre Nouveau MDP'))
               ->setFrom('reubenchouraki@vbladvisory.com')
               ->setTo($userEmail)
               ->setBody($this->renderView('security\email_message.html.twig',['randomMDP'=>$randomMDP]),'text/html'); 
                $mailer->send($message);
                $user[0]->setPassword($randomMDP);
                $hashedPassword=$hasher->encodePassword($user[0],$user[0]->getPassword());
                $user[0]->setPassword($hashedPassword);
                $em->persist($user[0]);
                $em->flush();
                $this->addFlash('success','votre nouveau mdp a été envoyé par mail, vieullez vous connecté et changé de mdp tout de suite,attention:les emails peuvent prendre plusiers minutes avant de vous etre envoyé');
                return $this->redirectToRoute('app_login');
            }
           
           
            else{
                $this->addFlash('warning','cette email n\'existe pas');
                return $this->redirectToRoute('app_login');
            } 

            
        }
        
        return $this->render('security/forgot_password.html.twig',['form'=>$form->createView()]);
    } 

    public function getRandomString($n) { 
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $randomString = ''; 
      
        for ($i = 0; $i < $n; $i++) { 
            $index = rand(0, strlen($characters) - 1); 
            $randomString .= $characters[$index]; 
        } 
      
        return $randomString; 
    } 

   


}