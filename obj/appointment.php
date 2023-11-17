<?php

    Class Appointment{

        private $property;
        private $tenants = array();
        private $time;
        private $duration;
        private $message;

        public function __construct($property, $tenant, $time, $duration=30){
            
            $this->property = $property;
            array_push($this->tenants, $tenant);
            $this->time = $time;
            $this->duration = $duration;

        }

        public function getProperty(){
            return $this->property;
        }

        public function getPropertyID(){
            return $this->property->getPropertyID();
        }

        public function getTenants(){
            return $this->tenants;
        }

        public function addTenant($tenant){
            array_push($this->tenants, $tenant);
        }

        public function updateTime($newTime){
            $this->time = $newTime;
        }

        public function getTime(){
            return $this->time;
        }
        public function getEndTime($format){
            return $this->time->modify("+$this->duration minutes")->format($format);
        }

        public function toConsole($final = false){

            $row = sprintf(
                "┃ %s│ %s│ %s│ %s┃\n",
                str_pad($this->time->format('d/m/y'), 13, " ", STR_PAD_RIGHT),
                str_pad($this->time->format('H:i') . '-' . $this->getEndTime('H:i'), 13, " ", STR_PAD_RIGHT),
                str_pad($this->property->getPropertyID(), 13, " ", STR_PAD_RIGHT),
                str_pad($this->tenants[0]->getFullName(), 31, " ", STR_PAD_RIGHT)
            );
            if(count($this->tenants) > 1){
                $row .= sprintf("┃ %-12s ┃ %-12s ┃ %-12s ┃ %-30s ┃\n", "Property ID", "Date", "Time", "Tenant(s)");
            }

            if($final){
                $row .= "┗" . str_repeat("━", 14) . "┷" . str_repeat("━", 14) . "┷" . str_repeat("━", 14) . "┷" .str_repeat("━", 32) ."┛\n";
            }
            else{
                $row .= "┠" . str_repeat("─", 14) . "┼" . str_repeat("─", 14) . "┼" . str_repeat("─", 14) . "┼" .str_repeat("─", 32) ."┨\n";
            }
            return $row;
        }

        private function tenantsToHTML(){
            $str = "";
            foreach($this->tenants as $tenant){
                $str .= $tenant->getFullName() . "<br>";
            }
            return $str;
        }

        private function tenantContactsToHTML(){
            $str = "";
            foreach($this->tenants as $tenant){
                $str .= $tenant->getContactNumber() . "<br>";
            }
            return $str;
        }

        public function toHTMLTable(){
            return sprintf(
                '<tr>
                    <td> %s </td>
                    <td> %s </td>
                    <td> %s </td>
                    <td> %s </td>
                <tr>
                ',
                $this->time->format('d/m/y'),
                $this->time->format('H:i') . "-" . $this->getEndTime('H:i'),
                $this->property->getPropertyID(),
                $this->tenantsToHTML()
            );
        }

        public function toActionTable(){
            return sprintf(
                '<tr>
                    <td> %s </td>
                    <td> %s </td>
                    <td> %s </td>
                    <td> %s </td>
                    <td> %s </td>
                <tr>
                ',
                $this->property->getPropertyID(),
                $this->time->format('d/m/y'),
                $this->tenantsToHTML(),
                $this->tenantContactsToHTML(),
                $this->message
            );
        }

        public function toJSON(){
            return json_encode($this);
        }

        public function setMessage($m){
            $this->message = $m;
        }

    }
?>