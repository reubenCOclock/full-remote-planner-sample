<?php
 namespace App\Utils;

 class Slugger{
     public function sluggify($stringToConvert){
       $sluggedName= preg_replace( '/[^a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*/', '-', strtolower(trim(strip_tags($stringToConvert))));
       return $sluggedName;
     }
 }



?>