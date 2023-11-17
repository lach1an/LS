<?php

use PHPUnit\Runner\FileDoesNotExistException;

    require_once "C:/Users/lachl/OneDrive/Desktop/LS/obj/appointment.php";
    require_once "C:/Users/lachl/OneDrive/Desktop/LS/obj/property.php";
    require_once "C:/Users/lachl/OneDrive/Desktop/LS/obj/tenant.php";
    require_once "C:/Users/lachl/OneDrive/Desktop/LS/obj/employee.php";

    Class Scheduler{
        
        private $pathToInput;
        private $rawAppointments = array();
        private $invalidInputs = array();
        private $employees = array();
        private $requireAction = array();
        private $filteredInputs = array();
        private $debug;

        public function rawInputLength(){
            return count($this->rawAppointments);
        }

        public function getFiltered(){
            return $this->filteredInputs;
        }

        public function getActionable(){
            return $this->requireAction;
        }

        public function __construct(String $path, $debug = false){ // init with path to the input CSV file

            if(!file_exists($path)){
                throw new Exception("The file: '$path' does not exist. Check the path is correct and try again.");
            }
            else if(strtoupper(pathinfo($path, PATHINFO_EXTENSION)) != "CSV"){
                throw new Exception("Only .csv files are supported. Please provide a .csv file and try again \n");
            }

            // set input path and if running in debug mode
            $this->debug = $debug;
            if($debug){
                echo "\n \033[42m ### RUNNING IN DEBUG MODE ###\033[49m \n\n";
            }
            $this->pathToInput = $path;
            return;
            
        }

        function parseInputCSV(){
            try{
                // open file specified at construct
                if(!$handle = fopen($this->pathToInput, "r")){
                    throw new Exception("Failed to open file '$this->pathToInput'. Check your permissions and try again.");
                }

                while(($data = fgetcsv($handle)) !== FALSE){
                    if(count($data) == 8){

                        // create tenant from the first 5 values
                        $tenant = new Tenant($data[0], $data[1], $data[2], $data[3], $data[4]);
                        // create property
                        $property = new Property($data[7]);
                        // parse date / time into  datetime obj
                        $dtStr = $data[5] .  " " . $data[6];
                        $timestamp = new DateTime($dtStr, new DateTimeZone("UTC"));

                        $app = new Appointment($property, $tenant, $timestamp);
                        array_push($this->rawAppointments, $app);
                    }
                    else{
                        echo "Failed to parse row. Incorrect number of inputs";
                        // store incorrectly formatted inputs to process later
                        array_push($this->invalidInputs, $data);
                    }
                }

                fclose($handle);

                // only for testing
                return $this->rawAppointments;

            } catch (Exception $e){
                echo "Failed to parse input file: '$this->pathToInput'";
                if($this->debug){
                    echo $e->getMessage();
                }
            }

        }

        // remove any appointments with invalid times / dates
        function filterApptTimes(){

            foreach($this->rawAppointments as $appointment){
                $date = $appointment->getTime();
                // assumes slightly extended working hours
                if($date->format('H') >= 8 && $date->format('H') <= 18){
                    // must be on the hour or half hour
                    if($date->format('i') == "00" || $date->format("i") == "30"){
                        // can't be on the final day of the month
                        if($date->format('j') != $date->format('t')){
                            // add to valid array and move to next appt
                            array_push($this->filteredInputs, $appointment);
                            continue;
                        }
                        else{
                            $appointment->setMessage("Appointments cannot be on the last day of the month.");
                            array_push($this->requireAction, $appointment);
                        }
                    }
                    else{
                        $appointment->setMessage("Appointments must be on the hour or on a half hour.");
                        array_push($this->requireAction, $appointment);
                    }
                }
                else{
                    $appointment->setMessage("Appointments must be within reasonable working hours.");
                    array_push($this->requireAction, $appointment);
                }
                // this whole nested else section could be done a lot better with a continue after the initial push but i couldn't get it to work
                
            }
        }

        function sortApptTimes(){

            // sorted for time in ascending order
            usort($this->filteredInputs, function($a, $b){
                return $a->getTime() > $b->getTime();
            });

        }

        function generateSchedules($nEmployees = 3){ // defaults to 3 employees but can be expanded

            // create employee handlers
            for($i=1; $i<$nEmployees+1; $i++){
                $employee = new Employee($i);
                array_push($this->employees, $employee);
            }


            foreach($this->filteredInputs as $aptmt){


                $aptmtScheduled = false;

                foreach($this->employees as $employee){

                    // if employee has no current appointments set
                    if(!$employee->currentApp){
                        $employee->addToSchedule($aptmt);
                        $aptmtScheduled = true;
                        break;
                    }
                    // assume appointments take 30 mins + 30 mins for travel time
                    else if($employee->calcDelta($aptmt->getTime()) >= 60){
    
                        $employee->addToSchedule($aptmt);
                        $aptmtScheduled = true;
                        break;
                    }
                    // if employee is already at the property then ignore travel time restrictions
                    else if($employee->currentApp->getPropertyID() == $aptmt->getPropertyID()){
                        $employee->addToSchedule($aptmt);
                        $aptmtScheduled = true;
                        break;
                    }

                }

                // if appointment couldn't be scheduled
                if(!$aptmtScheduled){
                    $aptmt->setMessage("All employees are busy at this time.");
                    array_push($this->requireAction, $aptmt);
                }
            }
        }

        function printSchedulesToDir($dir){
            try{
               
                if(!is_dir($dir)){
                    printf ("%-30s","Creating Folder: '$dir'");
                    if(!mkdir($dir, 0777, true)){
                        echo "\033[31m[FAIL]\033[39m \n\r";
                        throw new Exception("Failed to create directory: '$dir'");
                    }
                    echo "\033[32m" . "[OK]\033[39m \n\r";
                }


                foreach($this->employees as $key => $employee){
                    // make file to hold output
                    $filename = $dir."/schedule-".$employee->getID().".html";
                    printf ("%s %d/%-11d","Writing Schedule", $key+1, count($this->employees));
                    if($handle = fopen($filename, 'c')){
                        fwrite($handle,'<h1> Schedule For Employee #' . $employee->getID() . '<h1>');
                        fwrite(
                            $handle,
                            '<table>
                                <tr>
                                    <th> Date </th>
                                    <th> Time </th>
                                    <th> Property ID </th>
                                    <th> Tennant(s) </th>
                                </tr>'
                        );

                        foreach($employee->getSchedule() as $aptmt){
                            fwrite($handle, $aptmt->toHTMLTable());
                        }

                        fwrite($handle,'</table>');
                        fclose($handle);
                    }
                    else{
                        echo "\033[31m[FAIL]\033[39m \n\r";
                        throw new Exception("Failed to create file: '$filename'. Check permissions and try again\n");
                        
                    }
                    echo "\033[32m[OK]\033[39m \n\r";
                }

                if(count($this->requireAction) > 0){
                    $this->printActionableToHTML($dir);
                }

            } catch(Exception $e) {

                if($this->debug){
                    echo $e->getMessage();
                }
                else{
                    echo "Failed to write output to external file. Writing to console instead. \n";
                    $this->printSchedulesToConsole();
                }
            }

        }

        function printActionableToHTML($dir){
            $filename = $dir."/require_action.html";
            printf ("%-30s","Writing Actionable");
            if($handle = fopen($filename, 'c')){
                fwrite($handle,'<h1> Appointments Requiring Further Action <h1>');
                fwrite(
                    $handle,
                    '<table>
                        <tr>
                            <th> Property ID </th>
                            <th> Date </th>
                            <th> Tennant Name(s) </th>
                            <th> Tennant Contact(s) </th>
                            <th> Reason For Contact </th>
                        </tr>'
                );

                foreach($this->requireAction as $aptmt){
                    fwrite($handle, $aptmt->toActionTable());
                }

                fwrite($handle,'</table>');
                fclose($handle);
            }
            else{
                echo "\033[31m[FAIL]\033[39m \n\r";
                throw new Exception("Failed to create file: '$filename'. Check permissions and try again\n");
                
            }
            echo "\033[32m[OK]\033[39m \n\r";
        }

        function printSchedulesToConsole(){
            foreach($this->employees as $employee){
                if(count($employee->getSchedule()) > 0){
                    print("\n\rSchedule for Employee #".$employee->getID().":\n\r");
                    print("┏" . str_repeat("━", 14) . "┳" . str_repeat("━", 14) . "┳" . str_repeat("━", 14) . "┳" .str_repeat("━", 32) ."┓\n\r");
                    printf("┃ %-12s ┃ %-12s ┃ %-12s ┃ %-30s ┃\n", "Date", "Time", "Property ID", "Tenant(s)");
                    print("┣" . str_repeat("━", 14) . "╇" . str_repeat("━", 14) . "╇" . str_repeat("━", 14) . "╇" .str_repeat("━", 32) ."┫\n\r");
                    
                    foreach($employee->getSchedule() as $key => $app){
                        if($key == array_key_last($employee->getSchedule())){
                            print($app->toConsole(true));
                        }
                        else{
                            print($app->toConsole());
                        }
                    }
                }
            
            }

            if(count($this->requireAction) > 0){
                $this->printActionableToConsole();
            }
        }

        function printActionableToConsole(){
            // print the inputs that were not scheduled and the reasons why
            echo "\n" . count($this->requireAction) . " appointment(s) require further action.";
            if(count($this->requireAction) > 0){

                print("┏" . str_repeat("━", 14) . "┳" . str_repeat("━", 14) . "┳" . str_repeat("━", 32) . "┳" .str_repeat("━", 32) ."┓\n\r");
                printf("┃ %-12s ┃ %-12s ┃ %-12s ┃ %-30s ┃\n", "Date", "Property ID", "Reason", "Tenant Contact(s)");
                print("┣" . str_repeat("━", 14) . "╇" . str_repeat("━", 14) . "╇" . str_repeat("━", 32) . "╇" .str_repeat("━", 32) ."┫\n\r");

            }
        }

    }
?>