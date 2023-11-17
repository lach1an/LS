<?php
    Class Tenant{

        private $tenantID;
        private $contact;
        public $fName;
        public $lName;

        public function __construct($tenantID, $fName, $lName, $email, $phone){
            
            $this->tenantID = $tenantID;
            $this->fName = $fName;
            $this->lName = $lName;

            $this->contact = array();
            $this->setTenantContact($email, $phone);
        }

        public function setTenantContact($email, $phone){
            $this->contact["email"] = $email;
            $this->contact["phone"] = $phone;
        }

        public function getContactNumber(){
            return $this->contact["phone"];
        }

        public function getContactEmail(){
            return $this->contact["email"];
        }

        public function getFullName(){
            return $this->fName . " " . $this->lName;
        }

    }

?>