<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils,UserPasswordEncoderInterface $hasher): Response 
    { 
    
        /*
        $em=$this->getDoctrine()->getManager();
        $userRepo=$em->getRepository(User::class);
        $users=$userRepo->findAll();

        foreach($users as $user){
            $plainMDP=$user->getPassword();
            $hashedMDP=$hasher->encodePassword($user,$plainMDP);
            $user->setPassword($hashedMDP);
            $em->persist($user);
        }

        $em->flush();
        */

    
        

       
    
        
      
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        
      
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
       
    }

    
    /**
     *  Permet de se déconnecter
     *
     * @Route("/", name="app_logout")
     * 
     * @return void
     */
    public function logout() {
    }

}