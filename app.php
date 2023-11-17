<?php

    require_once "obj/Scheduler.php";

    // argv format --> option /input file / nEmployees / output file

    $debug = isset(getopt("d")["d"]) ? true : false; 
    if($debug){
        $offset = 1;
    }
    else{
        $offset = 0;
    }

    $path = isset($argv[1+$offset]) ? $argv[1+$offset] : false;
    $nEmployees = isset($argv[2+$offset]) ? $argv[2+$offset] : 3;
    $outputPath = isset($argv[3+$offset]) ? $argv[3+$offset] : false;

    $scheduler = false;
    do{
        if(!$path){
            $path = readline("Please enter the path to the file containing appointment data: \n");
        }
        try{
            $uT = hrtime(true);
            $scheduler = new Scheduler($path, $debug);
        }
        catch(Exception $e){
            echo $e->getMessage();
        }
    } while(!$scheduler);

    printf ("%-30s","Parsing input file");
    try{
        $scheduler->parseInputCSV();
        echo "\033[32m[OK]\033[39m \n\r";
    } catch(Exception $e){
        echo "\033[31m[FAIL]\033[39m \n\r";
        if($debug){
            echo $e->getMessage();
        }
        exit();
    }

    printf ("%-30s","Filtering appointments");
    $scheduler->filterApptTimes();
    echo "\033[32m[OK]\033[39m \n\r";

    printf ("%-30s","Sorting appointments");
    $scheduler->sortApptTimes();
    echo "\033[32m[OK]\033[39m \n\r";
    if(!$nEmployees){
        $nEmployees = readline("How many employees are available to attend appointments? (Default 3)\n");
    }
    printf ("%-30s","Generating Schedule(s)");
    try{
        $scheduler->generateSchedules($nEmployees ? $nEmployees : 3);
        $T = hrtime(true);   // stop clock

        echo "\033[32m[OK]\033[39m \n\r";
    }catch(Exception $e){
        echo "\033[31m[FAIL]\033[39m \n\r";
        if($debug){
            echo $e->getMessage();
        }
        exit();
    }

    if(!$outputPath && count($argv)<2){
        $outputPath = readline("Enter the path of the directory you wish to output the schedules to. (Leave blank to output to console)\n");
    }
    if($outputPath){
        $scheduler->printSchedulesToDir($outputPath);
    }
    else{

        $scheduler->printSchedulesToConsole();
    }

    if($debug){
        $deltaT = $T - $uT;
        $deltaTinMs = number_format($deltaT / 1000000, 2,".");
        $nAppointments = $scheduler->rawInputLength();
        echo "\n \033[42mCalculated schedule for $nAppointments appointments in ".$deltaTinMs. "ms\033[49m \n";
    }

    echo "All actions complete. Exiting";
    exit(0);


?>