<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;
use League\Csv\Writer;

include APPPATH . 'third_party/Filters.php';

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

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
class Cohorts extends REST_Controller {

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

        $this->load->model('classroom_model', 'classroom');

    }
    
    public function index_get()
    {
        //Start rate limiter
        $rateLimiter = ratelimiter();

        // Set file properties
		$observers = 'create_cohorts.csv';
        $local_path = APPPATH . '/imports/';
        
		// Fetch records 
        $iteratorRecords = $this->csv->getRecords($local_path . $observers);
        $records = iterator_to_array($iteratorRecords, true);

        //Set counter
        $counter = 0;
        $failed = 0;

        foreach($records as $record):
            $createParameters = array(
                "Name" => $record['Name'],
                "StartOn" => $record['2020-09-07T09:00:00.000Z'], //07-09-2020 (Start of term)
                "Archive" => false,
                "CompletionMessage" => "",
                "SendCompletionMessage"=> false,
                "IVFinalSignOff" => true
            );
            try {
                if ($counter == 0) {
                    echo $this->classroom->createClassroom($createParameters);
                    die();
                }
                ++$counter;
                $rateLimiter();
                echo "Created: ".$createParameters['Name'].PHP_EOL;
            } catch (Exception $e) {
                //echo 'Caught exception: ',  $e->getMessage(), "\n";
                ++$failed;
            }

        endforeach;

        echo PHP_EOL."Job completed. Classrooms created: $counter".PHP_EOL;


        //$return = array('status' => true, 'message' => "Job completed. Classrooms created: $counter");
        //$this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }    

}