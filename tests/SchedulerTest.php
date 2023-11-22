<?php

namespace Test\Php;

require_once dirname(__DIR__, 1) . "/obj/scheduler.php";
require_once dirname(__DIR__, 1) . "/obj/appointment.php";
require_once dirname(__DIR__, 1) . "/obj/property.php";
require_once dirname(__DIR__, 1) . "/obj/tenant.php";

use PHPUnit\Framework\TestCase;
use SebastianBergmann\Type\VoidType;

    final class SchedulerTest extends TestCase{

        public function testFileOpening(): void{
            try{
                $scheduler = new \Scheduler(dirname(__DIR__, 1) . "/sample/sample30.csv");
                $this->assertTrue(true);
            }
            catch(\Exception $e){
                $this->fail($e->getMessage());
            }
        }

        public function testgetCSV(): void{
            $scheduler = new \Scheduler(dirname(__DIR__, 1) . "/sample/sample30.csv");
            $parsedInput = $scheduler->parseInputCSV();

            $this->assertEquals(30, count($parsedInput));
        }

        public function testCSVObjParsing(): void{

            $expectedApp = new \Appointment(new \Property(9876), new \Tenant(123, "John", "Doe", "john.doe@example.com", 12345678), new \DateTime("01/09/2023 09:00")); 

            $scheduler = new \Scheduler(dirname(__DIR__, 1) . "/sample/sample1.csv");
            $parsedInput = $scheduler->parseInputCSV();

            $this->assertEquals($expectedApp, $parsedInput[0]);
        }

        public function testInputFilteringPos(): void{
            
            $scheduler = new \Scheduler(dirname(__DIR__, 1) . "/sample/sample4.csv");
            $parsed = $scheduler->parseInputCSV();
            
            $this->assertEquals(4, count($parsed));

            $scheduler->filterApptTimes();

            // should be two appointments with valid times / syntax
            $this->assertEquals(2, count($scheduler->getFiltered()));

        }

        public function testInputFilteringNeg(): void{
            
            $scheduler = new \Scheduler(dirname(__DIR__, 1) . "/sample/sample4.csv");
            $parsed = $scheduler->parseInputCSV();
            
            $this->assertEquals(4, count($parsed));
            
            $scheduler->filterApptTimes();

            // should be two appointments filtered out
            $this->assertEquals(2, count($scheduler->getActionable()));

        }

        public function testInputTimeSorting(): void{

            $scheduler = new \Scheduler(dirname(__DIR__, 1) . "/sample/sample30.csv");
            $parsed = $scheduler->parseInputCSV();
            
            $this->assertEquals(30, count($parsed));
            
            $scheduler->filterApptTimes();

            $scheduler->sortApptTimes();

            // only checking the propertyID values match order for convinience
            $expectedOrder = [155,423,899,927,849,615,145,713,993,128,835,285,859,700,279,354,985,335,473,873,613,208,505,571,201,780,848,263,896,832];

            $returnedOrder = $scheduler->getFiltered();

            for($i=0; $i<count($returnedOrder); $i++){
                
                $this->assertEquals($expectedOrder[$i], (int)$returnedOrder[$i]->getProperty()->getPropertyID());

            }

        }

    }

?>