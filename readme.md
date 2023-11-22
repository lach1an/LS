# Tenant Appointment Booking Scheduler - TABS

## What is TABS?

TABS is a PHP console application designed to parse tenant move-in appointments and create optimal schedules for workers to carry out these move-in checks.\
Once generated these schedules can either be displayed within the console, or saved as a series of HTML files.


## Features
- CSV input file parsing
- Text-based UI
- Employee schedule generation
- Visual schedule display within the console
- Multiple schedule output to HTML file(s)

## Documentation

### Installation & Setup

#### Notice: ####
**TABS is intended for usage with PHP 8+.** 
Therefore TABSlication may behave unexpectedly when run using an older version of PHP.  

A PHP 8 installer can be found [here.](https://www.php.net/downloads.php)  


To follow the installation guide below please ensure PHP is set up as an environment variable, and that git is installed on your machine.

#### Installation ####
1. Create a new directory on your machine and create a git instance with the terminal command `git init`
2. Clone this repository into your local directory with `git clone https://github.com/lach1an/LS/`

### CSV Input Syntax

The scheduler requires a pre-formatted CSV file containing the following information, in the following order:
  
1. Tenant ID
   
3. First name of tenant

4. Last name of tenant

5. Tenant's email

6. Tenant's phone number

7. The date of the tenants desired appointment - in Y-m-d format

8. The time of the tenant's desired appointment

9. The ID of the property the tenant is moving in to

**If any of this information is incorrectly formatted it will be ignored by the scheduler.**  

Sample input files can be found in the [sample/](https://github.com/lach1an/LS/tree/main/sample) directory 

## How to use TABS?

### Options & Arguments

While a text based UI is provided TABS is intended to be run as a single command, passing any relevant paramaters as arguments from the terminal.  
TABS can be run using the command `php app.php` (when LS is the active directory) and then arguments and options appear afterwards. 

**Arguments must be provided in the order listed below, no arguments can be omitted or passed over. E.g. to specify the output directory you must also specify the number of employees available.**

#### Argument 1: Input File ####
The first argument provided is taken as the *relative* file path from the root directory to the CSV file you wish to parse. For example:  
`php app.php sample/sample30.csv`  

**Notice:**  
When running the scheduler and only providing one argument, all other arguments will be set to their default values, which is that there are 3 employees available, and to output the schedule to the console window. 

#### Argument 2: Available Employees ####
The second argument is the number of available employees, this sets how many employees are able to attend an appointment in the event another is occupied. It is recommended to scale this with the number of applications to avoid having to reschedule a large quantity of them.  
This is passed to the scheduler as a single integer like so:  
`php app.php sample/sample30.csv 3`

#### Argument 3: Output Directory ####
If you do not wish to save the generated schedules as individual HTML files, simply omit this argument.  
Otherwise provide the *relative* path to an existing directory for the output files to be placed in, or for a new directory that you wish to create.  

E.g:  
`php app.php sample/sample30.csv 3 output`  
Will write the HTML output files to a directory called output.  

**Notice:**
If there are any existing files in the chosen output directory that match the naming scheme of `schedule-[employeeID].html` they will be overwritten. Take care to ensure no data is lost.

#### Debug Mode ####
To view timing data, or verbose error messages it is possible to run TABS in debug mode, to do so pass the option `-d` before your first argument. E.g:
`php app.php -d smaple/sample3-.csv 3 output`  
This is not recommended for most users but may be useful for debugging and/or dealing with any issues within TABS.

### Text Interface
TABS also contains a text based interface for users who may be unfamilliar with using a CLI. The text based interface provides all of the same functionality as passing arguments, however it guides the user through the process more and is capable of handling incorrect inputs.  

TABS can be run with the text UI using the command:  `php app.php`  

## Testing

A small PHPUnit test suite is included with this repo. It solely aims to check the functionality of the Scheduler object.  
The test suite can be run using the command `./vendor/bin/phpunit tests`


