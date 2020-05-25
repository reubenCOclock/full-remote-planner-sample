<?php
 namespace App\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\User;
use App\Entity\Role;
use App\Entity\PasswordUpdate;
use App\Entity\ConsultantProjectPricesRegie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request; 
use App\Entity\Vacation;
use App\Form\VacationType;
use App\Entity\Formation;
use App\Form\ProjectRegieType;
use App\Form\ProjectForfaitType;
use App\Form\FormationType;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Form\UserClientType;
use App\Form\UserRHType;
use App\Utils\Slugger;
use App\Entity\Feedback;
use App\Entity\SickDay;
use App\Form\AccountType;
use App\Entity\MonthlySummary;
use App\Form\FacturationFormType;
use App\Entity\MyDocument;
use App\Form\PasswordUpdateType;
use Doctrine\Common\Collections\Collection;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\ProjectForfaitLivrables;


class AdminMonthlySummController extends AdminUtilsController{
    
}






?>