<?php
 namespace App\Entity;

class ForgotPassword {
    private $email;

    public function setEmail($email){
        $this->email=$email;
    } 

    public function getEmail(){
        return $this->email;
    }
}

?>