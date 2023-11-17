<?php

    class Employee {

        private $id;
        public $fname;
        public $lname;
        public $email;
        public $phone;
        // schedule is a 2d assoc array of appointments sorted by the day they occur on
        private $schedule = array();
        public $currentApp= false;

        public function __construct($id) {
            $this->id = $id;
        }

        public function addToSchedule($app){

            // if employee doesn't have a schedule for this day yet
            // if(!array_key_exists($date, $this->schedule)){
            //     $this->schedule[$date] = array();
            // }

            array_push($this->schedule, $app);
            $this->currentApp = $app;
        }

        public function calcDelta($appTime){ // returns the time difference in minutes between an employees current appointment and their next one
            if($this->currentApp){
                $delta = $this->currentApp->getTime()->diff($appTime);
                $deltaS = (new DateTime())->setTimeStamp(0)->add($delta)->getTimeStamp();
                // convert to minutes
                return $deltaS/60;
            }
        }

        public function getID(){ return $this->id; }
        public function getSchedule(){ return $this->schedule; }

        public function getFullName(){ return $this->fname . " ". $this->lname;}
    }

?>