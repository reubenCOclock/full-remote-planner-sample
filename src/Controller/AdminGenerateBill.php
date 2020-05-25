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

  

    class AdminGenerateBill extends AdminControllerRC { 

        /**
         * @Route("/admin/{slug}/facturationClient/",name="facturationClient")
         */ 
        public function generateClientBill(Request $request){ 
            $user=$this->getUser();
            // creation d'un formulaire pour saisir le mois et l'anée pour consulter la facturation
            $form=$this->createForm(FacturationFormType::class);
            $form->handleRequest($request);
            $em=$this->getDoctrine()->getManager();
            
            $userRepo=$em->getRepository(User::class);
            // recuperation de tous les consultants active en bdd
            $consultants=$userRepo->findAllActiveConsultants();

            $recapRepo=$em->getRepository(MonthlySummary::class);
            $factureRepo=$em->getRepository(MyDocument::class);
            
            if($form->isSubmitted() && $form->isValid()){ 
                $errors=false;
                // recuperation des valeurs saisi dans le formualire
                $month=intval($form->get('month')->getViewData());
                 $year=intval($form->get('year')->getViewData());
                 $yearSubStr=intval(substr($year,2));
                 
                 // creation d'une session pour pouvoir utiliser des valeurs provenant du for
                $session=new Session();
                $session->set('facturationMonth',$month);

                $session->set('facturationYear',$yearSubStr);

            
               // creation d'un tablau vide 
                $consultantRecapsArray=[];
                $consultantsWithEmptyRecaps=[];
                foreach($consultants as $consultant){
                    // a chaque tour de boucle, je recupere les recaps du consultant pour le mois et l'anée saisi en formualire
                    $userSummary=$recapRepo->getRecapsByUserAndMonth($consultant->getId(),$month,$yearSubStr);
                    if(!empty($userSummary)){
                        // si il y a bien un/des ,j'alimente le tableau des consultant avec leur recap
                        $consultantRecapsArray[]=$userSummary;
                    } 
                    
                    else{ 
                        // sinon j'alimente le tableau des consultant sans leur recap
                        $consultantsWithEmptyRecaps[]=$consultant;
                    }

                } 
                // si il y a des consultants qui n'ont pas soumis leur recap pour le mois,je sors de la fonction pour prevenir que des consultants n'ont pas soumis leur recapulatif
                if(!empty($consultantsWithEmptyRecaps)){
                    
                    return $this->render('admin/empty_consultant_alert.html.twig',['consultantsWithEmptyRecaps'=>$consultantsWithEmptyRecaps]);
                    
                }
                
                $date=new \DateTIme();

               
               
                $referenceDateFormat=$date->format('Y-m-d');
                
               
                // manipulation de la syntaxe d'affichage de la date en cours ($date)
                $facturationMonth=intval(substr($referenceDateFormat,5,2));
                
                $facturationYear=intval(substr($referenceDateFormat,2,2));
                // verification que il n'y a pas deja des factures crée pour ce mois
                if(!empty($this->testEmptyFactures($facturationMonth,$facturationYear))){
                    $errors=true;
                    $this->addFlash('warning','vous avez deja generé des factures pour le mois de'.' '.$this->getActualMonthFromInt($facturationMonth).' '.'avant de regenerer les factures, vielliez bien supprimer les factures de ce mois');
                } 

                if($errors == false){
    
               // si il n'y a pas d'erreurs, recuperation du dernier recap pour le consultant en appelant la fonction getLastMonthlyRecaps
                $lastMonthlyRecapArray=$this->getLastMonthlyRecap($consultantRecapsArray);
                // creation des factures pour le mois selon les recaps mensuels
                $this->createFactureDocuments($lastMonthlyRecapArray); 

                $this->addFlash('success','les factures ont bien ete crée');
                return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
                }
                
            } 

            return $this->render('admin/facturation_client.html.twig',['form'=>$form->createView()]);
        } 

   
        
        
        
        /**
         * @Route("/admin/continueEmptyConsultantChoice/", name="continueEmptyConsultantChoice")
         */

        // si il y a des consultants qui n'ont pas soumi leur recap mensuel cette fonction sera appelé
        public function executeEmptyConsultantChoice(Request $request){ 
           
                $currentUser=$this->getUser();
                $errors=false;
                $session=new Session();
                // recuperation du choix de l'admin si il veux continuer la facturation meme si des consultants n'ont pas soumis leur recapulatif mensuel
                $data=json_decode($request->getContent(),true);
                if($data['value']==='Oui'){
                    // si l'admin choisi de continuer la facturation
                    
                    $em=$this->getDoctrine()->getManager();
                    $userRepo=$em->getRepository(User::class);
                    $consultantRecapsArray=[];
                    // recuperation des consultants actifs
                    $consultants=$userRepo->findAllActiveConsultants();
                    $recapRepo=$em->getRepository(MonthlySummary::class);
                    $factureRepo=$em->getRepository(MyDocument::class);  

                    

                   $date=new \DateTIme();

                   $referenceDateFormat=$date->format('Y-m-d');
                
                   $facturationMonth=intval(substr($referenceDateFormat,5,2));
                
                   $facturationYear=intval(substr($referenceDateFormat,2,2));
 
                    foreach($consultants as $consultant){ 
                        // creation d'un tableau avec tous les recaps des consultants qui ont soumis un recap pour le mois
                        $userSummary=$recapRepo->getRecapsByUserAndMonth($consultant->getId(),$session->get('facturationMonth'),$session->get('facturationYear'));
                        if(!empty($userSummary)){
                            $consultantRecapsArray[]=$userSummary;
                        } 
                        
                    } 
                    
                   

                    
                    // si il y a pas deja des factures pour le mois 
                    if(!empty($this->testEmptyFactures($facturationMonth,$facturationYear))){
                        $errors=true;
                        return new Response('les factures ont deja été géneré pour le mois de'.' '.$this->getActualMonthFromInt($facturationMonth).' '.'avant de regenerer les factures vieullez bien supprimer les factures de ce moirs');
                    }  

                    if($errors==false){
                    // sinon,encore recuperation de la derniere facture pour chaque consultant 
                      $lastMonthlyRecapArray=$this->getLastMonthlyRecap($consultantRecapsArray);
                      // creation des factures
                      $this->createFactureDocuments($lastMonthlyRecapArray);
                      return new Response('factures pour'.' '.$this->getActualMonthFromInt($facturationMonth).' '.'generé');
                    }
                     
                } 

                else if($data['value']==='Non'){
                    return new Response ('process aborted');
                }
                
                
               
        }
        
    

        public function getLastMonthlyRecap($recapsArray){ 
            // creation d'un tableau vide 
            $lastRecapArray=[]; 
            // boucle sur les recaps par consultant 
            foreach($recapsArray as $recaps){
                foreach($recaps as $index=>$recap){
                   // pour chaque recaps que le consultant a soumis, si ce le dernier recap, il est ajouteé au tableau
                    if($index==count($recaps)-1){
                        $lastRecapArray[]=$recap;
                        
                    }
                }
            } 

          
            return $lastRecapArray;
        } 


        public function testEmptyFactures($month,$year){
            $em=$this->getDoctrine()->getManager();

            $factureRepo=$em->getRepository(MyDocument::class);
        
            $facturesPresent=$factureRepo->findBy(['category'=>'facture','month'=>$month,'year'=>$year]);

            return $facturesPresent;
        } 


        /**
         * @Route("admin/{slug}/deleteBillsForMonthAndYear", name="deleteBillsForMonthAndYear")
         */

        public function deleteBillsForMonthAndYear(){
            $user = $this->getUser();


            $now=new \DateTIme();

            $dateFormat=$now->format('Y-m-d');

            
            $facturationMonth=substr($dateFormat,5,2);
            
            $facturationYear=substr($dateFormat,2,2);

            $em=$this->getDoctrine()->getManager();

            $factureRepo=$em->getRepository(MyDocument::class); 



            $facturesByMonthAndYear=$factureRepo->findBy(['category'=>'facture','month'=>$facturationMonth,'year'=>$facturationYear]);

         

            foreach($facturesByMonthAndYear as $facture){
               
                $em->remove($facture);
                
            }

         

            $em->flush();

            $this->addFlash('warning','les factures pour le mois de'.' '.$this->getActualMonthFromInt($facturationMonth).' '.'ont bien ete supprimé');

            return $this->redirectToRoute('userpage',['slug'=>$user->getSlug()]);
        }

       

        public function createFactureDocuments($lastMonthlyRecapArray) { 

            $counter=0;

            $errors=false;
            

           

            $date=new \DateTIme();
            
            // date affiché sur le template twig

            $dateFormat=$date->format('Y-m-d H:i:s').$date->getTimeStamp();

           // date affiché sur la reference de la facture
            $referenceDateFormat=$date->format('Y-m-d');

            
            // date affiché sur la facture

            $factureDateFormat=$date->format('d/m/Y');
            
            $facturationDirectory=$this->getParameter('facturation_directory');
            $publicDirectory=$this->getParameter('doc_directory'); 

            $em=$this->getDoctrine()->getManager();

            $userRepo=$em->getRepository(User::class); 




            // je boucle sur le tableau contenant le dernier recaps du mois soumi par le consultant

           
       
            foreach($lastMonthlyRecapArray as $recap) { 

        
              
                // a chaque tour de boucle je recupere les infos du consultant
                $consultantName=$recap->getConsultant()->getFirstName();
                $consultantId=$recap->getConsultant()->getId();
                // je recupere l'entité qui contient les projets du consultant
                $consultantProjects=$recap->getProjectDays()->toArray();
               
                // je recupere le mois
                $billingMonth=$recap->getMonth();


                $actualMonth=$this->getActualMonthFromInt($billingMonth);

                // je recupere l'anee
                $billingYear=$recap->getYear();

            

                // je boucle sur l'entitre contenant les projets du consultant
                foreach($consultantProjects as $project){ 
                   
                    
                    
                    $projects=$project->getProject()->getConsultants()->toArray();
                    
                    

                    // si le projet a bien un client I.E si il n'est pas de type intercontrat
                    if($project->getProject()->getClient()!=null){ 

                      
                       // si le projet est de type regie
                       if($project->getProject()->getContractType()=='Regis') { 
                           
                        
                        // je recupere l'entite (sous forme de tableau) contenant les consultants et leur prix pour le projet
                         $projectRegiePriceDetails=$project->getProject()->getConsultantProjectPricesRegies()->toArray();

                        
                         // je boucle sur cette entité (sous forme de tableau)
                         foreach($projectRegiePriceDetails as $index=>$projectPriceRegieDetail){ 
                             // si le projet contenu dans le tableau est le meme que celui sue lequel on est en trien de boucler
                          if($projectPriceRegieDetail->getProject()->getTitle()==$project->getProject()->getTitle()){ 
                              
                              // si le consultant contenu dans le tableau correspond au consultant du projet contenu dans la boucle (le projet de l'entité projectDays du recao)
                            if($projectPriceRegieDetail->getConsultant()->getFirstName()==$consultantName){ 
                              
                                $counter=$counter+1;

                                
                              // recuperation des informations du projet 
                                $projectId=$project->getProject()->getId();
                                $projectName=$projectPriceRegieDetail->getProject()->getTitle();
                                $clientName=$projectPriceRegieDetail->getProject()->getClient()->getFirstName();
                                $clientCompany=$projectPriceRegieDetail->getProject()->getClient()->getCompanyName();
                                $clientAdress=$projectPriceRegieDetail->getProject()->getClient()->getAdress();
                                $clientAdressePostal=$projectPriceRegieDetail->getProject()->getClient()->getAdresseCodePostal();
                                $clientPhone=$projectPriceRegieDetail->getProject()->getClient()->getPhoneNumber();
                                $clientId=$projectPriceRegieDetail->getProject()->getClient()->getId();

                                $daysWorked=$project->getDays();

                                // recuperation du prix au jour du consultant
                                $amountCharged=$daysWorked * $projectPriceRegieDetail->getPrice();
                                $consultantRate=$projectPriceRegieDetail->getPrice();
                                
                                
                               
                                // creation d'une facture
                                $facture=new MyDocument();
                                $options=new Options();
                                $options->set('isRemoteEnabled',true);
                                $domPDF=new Dompdf($options);
                                
                                // creation de la vue generique pour rendre cette facture
                                $html=$this->renderView('billing/billing_template.html.twig',['consultantName'=>$consultantName,'projectName'=>$projectName,'billingMonth'=>$billingMonth,'daysWorked'=>$daysWorked,'clientName'=>$clientName,'montant'=>$amountCharged,'billingYear'=>$billingYear,'reference'=>$referenceDateFormat.$consultantId.$clientId,'clientAdress'=>$clientAdress,'clientPhone'=>$clientPhone,'clientCompany'=>$clientCompany,'actualMonth'=>$actualMonth,'projectPrice'=>$consultantRate,'factureDateFormat'=>$factureDateFormat,'clientAdressePostal'=>$clientAdressePostal]);
                                $htmlBillingView=$facturationDirectory.'/'.$clientName.$consultantName.$dateFormat.'.facture'.$counter.'.html.twig';
                               
                                file_put_contents($htmlBillingView,$html);

                                // creation d'un document pdf selon la vue html 

                                $domPDF->loadHtml($html);
                                $domPDF->setPaper('A4','portrait');
                            
                                $domPDF->render();

                                
                                $output=$domPDF->output();

                                // emplacement du fichier pdf 
                                $pdfBillingFile=$publicDirectory.'/'.$clientName.$consultantName.$dateFormat.'.'.$counter.'.pdf';
                                file_put_contents($pdfBillingFile,$output);
                                //$start=strrpos($pdfBillingFile,'/');
                                //$end=strrpos($pdfBillingFile,'f');
                                //$urlToSet=substr($pdfBillingFile,$start+1,($end-$start+1));

                                // alimntation et persistance de l'entité facture

                                $facture->setTitle($pdfBillingFile);
                                $facture->setCategory('facture');
                                $facture->setUrl($pdfBillingFile);
                                $facture->setClient($project->getProject()->getClient());
                                $facture->setProject($project->getProject());
                                $facture->setConsultant($userRepo->associateBillToConsultant($consultantName)[0]);
                                $facture->setMonth($billingMonth);
                                $facture->setDays($daysWorked);
                                $facture->setConsultantRate($projectPriceRegieDetail->getPrice());
                                $facture->setContractType($projectPriceRegieDetail->getProject()->getContractType());
                                $facture->setYear($billingYear);
                                $em->persist($facture);
                               

                             

                              

                               }
                                
                                
                    
                             }
                               
                            }
                         }
                                

                 
                    // si le projet est de type forfait
                    else if($project->getProject()->getContractType()=='Forfait'){

                       // je recupere les informations du projet
                        $counter=$counter+1;
                        $projectName=$project->getProject()->getTitle();
                        $clientName=$project->getProject()->getClient()->getFirstName();
                        $clientCompany=$project->getProject()->getClient()->getCompanyName();
                        $clientAdress=$project->getProject()->getClient()->getAdress();
                        $clientAdressePostal=$project->getProject()->getClient()->getAdresseCodePostal();
                        $clientPhone=$project->getProject()->getClient()->getPhoneNumber();
                        $clientId=$project->getProject()->getClient()->getId();
                        $daysWorked=$project->getDays();


                         
                        // je recupere les livrables correspondant a ce projet
                        $projectLivrables=$project->getProject()->getProjectForfaitLivrables()->toArray();
                         
                        // je boucle sur les livrables de ce projet
                    foreach($projectLivrables as $livrable){
                        
                        
                               
                      if(intval($livrable->getDate()->format('m'))==$billingMonth){
                          // si le mois du livrable correspond au mois en cours
                            $facture=new MyDocument();
                            $options=new Options();
                            $options->set('isRemoteEnabled',true);
                               
                            $domPDF=new Dompdf($options);
                               
            
            
                           // creation de la vue html avec les informations correspondant au projet
                            $html=$this->renderView('billing/billing_forfait.html.twig',['consultantName'=>$consultantName,'projectName'=>$projectName,'billingMonth'=>$billingMonth,'daysWorked'=>$daysWorked,'clientName'=>$clientName,'billingYear'=>$billingYear,'reference'=>$referenceDateFormat.$consultantId.$clientId,'clientAdress'=>$clientAdress,'clientPhone'=>$clientPhone,'clientCompany'=>$clientCompany,'actualMonth'=>$actualMonth,'projectPrice'=>$livrable->getMontant(),'livrable'=>$livrable->getLivrable(),'factureDateFormat'=>$factureDateFormat,'clientAdressePostal'=>$clientAdressePostal]);
                            $htmlBillingView=$facturationDirectory.'/'.$clientName.$dateFormat.'forfait.html.twig';
                            
                           
                            file_put_contents($htmlBillingView,$html);
            

                            $domPDF->loadHtml($html);
                            $domPDF->setPaper('A4','portrait');
                            $domPDF->render();
            
            
                            $output=$domPDF->output();
                            $pdfBillingFile=$publicDirectory.'/'.$consultantName.$clientName.$dateFormat.$counter.'.pdf';
                            file_put_contents($pdfBillingFile,$output);
                            $start=strrpos($pdfBillingFile,'/');
                            $end=strrpos($pdfBillingFile,'f');
                            $urlToSet=substr($pdfBillingFile,$start+1,($end-$start+1));
            
            
                            $facture->setCategory('facture');
                            $facture->setUrl($pdfBillingFile);
                            $facture->setClient($project->getProject()->getClient());
                            $facture->setProject($project->getProject());
                            $facture->setConsultant($recap->getConsultant());
                            $facture->setMonth($billingMonth);
                            $facture->setDays($daysWorked);
                            $facture->setYear($billingYear);
                            $facture->setContractType($project->getProject()->getContractType());
                            $em->persist($facture);
                            
                            
                               }
                              
                           }
                       }
                    }
                    
                }
                
            } 
             
      
         // insertion en bdd des entités persisté
         $em->flush();
           
  
        } 


    

        /**
         * @Route("/admin/{slug}/setMoisConsultationFactures/", name="setMoisConsultationFactures")
         */

         // fonction permettant de voir les factures par mois
        public function viewFacturationMonth(Request $request){
            $em=$this->getDoctrine()->getManager();
            $documentRepo=$em->getRepository(MyDocument::class);
            $currentUser=$this->getUser();
            $form=$this->createForm(ViewFacturesByMonthType::class);
            $form->handleRequest($request);
            
            if($form->isSubmitted() && $form->isValid()){
              
                $year=intval($form->get('year')->getViewData());
                $consultationYear=intval((substr($year,2)));
                $month=intval($form->get('month')->getViewData());
                $actualMonth=$this->getActualMonthFromInt($month);
                $factures=$documentRepo->getFactureDocumentsByMonthAndYear($month,$consultationYear);
                return $this->render('admin/view_factures_by_month.html.twig',['factures'=>$factures,'actualMonth'=>$actualMonth]);
                
            }

            return $this->render('admin/select_facturation_month.html.twig',['form'=>$form->createView()]);
        } 

        /**
         * @Route("admin/{slug}/downloanFacture/{id}", name="downloadFacture")
         */
        
        
        public function downloadBill($id){
            $em=$this->getDoctrine()->getManager();
            
            $documentRepo=$em->getRepository(MyDocument::class);
            $document=$documentRepo->find($id);
          
            

            $response = new Response();
            $response->setContent(file_get_contents($document->getUrl()));
            $response->headers->set(
                'Content-Type',
                'application/pdf'
            ); // Affiche le pdf au lieux de le télécharger
            $response->headers->set('Content-disposition', 'filename=' . $document->getTitle());
      
            return $response;
        }


    
        
        
    
    }



?>