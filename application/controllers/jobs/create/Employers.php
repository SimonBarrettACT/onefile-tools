<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;
use League\Csv\Writer;

include APPPATH . 'third_party/Filters.php';

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . 'libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Employers extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        $apiKey = $this->uri->segment(2);

        if (env('MY_API_KEY') !== $apiKey ):
            // Set the response and exit
            $this->response([
                'status' => FALSE,
                'error' => 'You do not have authorization to access this Job.'
            ], REST_Controller::HTTP_UNAUTHORIZED); // NOT_FOUND (404) being the HTTP response code
        endif;

        $this->load->model('user_model', 'user');

    }
    
    public function index_get()
    {
        //Start rate limiter
        $rateLimiter = ratelimiter();

        // Set file properties
		$observers = 'create_employers.csv';
        $local_path = APPPATH . '/imports/';
        
		// Fetch records 
        $iteratorRecords = $this->csv->getRecords($local_path . $observers);
        $records = iterator_to_array($iteratorRecords, true);

        //Set counter
        $counter = 0;
        $failed = 0;
        $employer = null;


        foreach($records as $record):

            //Fetch Learner
            $learners = json_decode($this->user->getUsers(array(
                'FirstName' => trim($record['FirstName']),
                'LastName' => trim($record['LastName']),
                'Role' => 1,
            )), true);

            if ($learners[0]) {

                $createParameters = array(
                    "FirstName" => trim($record['EmployerFirst']),
                    "LastName" => trim($record['EmployerLast']),
                    "Email" => trim($record['EmployerEmail']),
                    "PlacementID" => 149866,
                    "Role" => 40
                );

                $employers = json_decode($this->user->getUsers($createParameters), true);


                try {
                    if (!$employers) {
                        //Create the employer
                        $employer = $this->user->createUser($createParameters);
                        $employer = json_decode($employer, true);
                        echo 'Employer Created: ' . trim($record['EmployerFirst']) . ' ' . trim($record['EmployerLast']) . PHP_EOL;
                        ++$counter;
                    } else {
                        $employer = $employers[0];
                        echo 'Employer Exists: ' . trim($record['EmployerFirst']) . ' ' . trim($record['EmployerLast']) . PHP_EOL;
                    }

                    //Assign the placement
                    $this->user->setPlacement($employer['ID'], $createParameters['PlacementID']);
                    echo 'Placement Assigned'.PHP_EOL;

                    //Set user placement
                    $assignParameters = array('PlacementID' => $createParameters['PlacementID']);
                    $this->user->updateUser($learners[0]['ID'], $assignParameters);
                    echo 'Learner Placement Set'.PHP_EOL;

                    //Assign to the learner
                    $assignParameters = array('LearnerID' => $learners[0]['ID'], 'Level' => 1);
                    $this->user->assignUser($employer['ID'], $assignParameters);

                    echo 'Learner Assigned'.PHP_EOL;
                    echo '============================================'.PHP_EOL;

                } catch (Exception $e) {
                    echo 'Error creating ' . $record['EmployerFirst'] . '' . $record['EmployerLast'] . PHP_EOL;
                    echo '============================================'.PHP_EOL;
                    ++$failed;
                }

            }

        endforeach;

        $return = array('status' => true, 'message' => "Job completed. Accounts created: $counter");
        $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }    

}
