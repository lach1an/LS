<?php

    class Property{

        private $propertyID;

        public function __construct($propertyID) {
            $this->propertyID = $propertyID;    
        }

        public function getPropertyID() {
            return $this->propertyID;
        }


    }

?>