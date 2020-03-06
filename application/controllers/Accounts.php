<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounts extends CI_Controller {

    protected $assessors;
    protected $iqas;
    protected $learners;

    public function __construct()
    {
            parent::__construct();

            if (!is_cli()) :
                // echo "<p>These tools can only be run from the command line.</p>";
                // die();
            endif;

            //Get the assessors
            $this->assessors = [];
            for ($x = 186270; $x <= 186289; $x++) {
                $this->assessors[] = $x; 
            }

            //Set the iqas
            $this->iqas = [];
            for ($x = 186290; $x <= 186293; $x++) {
                $this->iqas[] = $x; 
            }

            //Set learners
            $this->learners = [];
            for ($x = 186565; $x <= 186659; $x++) {
                $this->learners[] = $x; 
            }


    }
    
    public function index()
    {
      echo "Help will appear here. Eventually!".PHP_EOL;     
    }

    public function users($role=1)
    {
        echo $this->user->getUsers($role);
    }

    public function user($id)
    {
        echo $this->user->getUser($id);
    }

    public function fetch($file='learners'){
        $records = $this->import->getFile($file);
        $counter = 1;

        write_file(APPPATH . "logs/passwords_$file.json", '[');

        foreach($records as $record):
            unset($record['id']);
            
            switch ($file) {
                case "learners":
                    if ($counter >= count($this->assessors)) {$counter = 0;}
                    $record['Role'] = 1;
                    $record['DefaultAssessorID'] = $this->assessors[$counter++];
                    $record['ClassroomID'] = 10540;
                    $record['PlacementID'] = 7359;
                    break;
                case "assessors":
                    $record['Role'] = 5;
                    break;
                case "iqas":
                    $record['Role'] = 10;
                    break;
                case "employers":
                    $record['Role'] = 40;
                    break;
                default:
                    
            }
            sleep(2);
            $response = $this->user->createUser($record);
            write_file(APPPATH . "logs/passwords_$file.json", $response.", ", 'a+');
        endforeach;

        write_file(APPPATH . "logs/passwords_$file.json", ']', 'a+');

        echo 'Creation complete';

    }


    public function archive($id=0){
        if ($id == 0):
            foreach($this->learners as $learner):
                sleep(2);
                try {
                    $this->user->archiveUser($learner);
                } catch (Exception $e) {
                    //Ignore
                }
                
            endforeach;
            echo 'Complete';
        else:
            $this->user->archiveUser($id);
        endif;
        
    }

}
