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
class Missing extends REST_Controller {

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

        $local_path = APPPATH . '/tmp/';
        $header = ['FirstName', 'LastName', 'AssessorFirst', 'AssessorLast', 'EmployerFirst', 'EmployerLast', 'EmployerEmail'];
        $filename = 'missing_accounts.csv';

        $writer = Writer::createFromPath($local_path . $filename, 'w');

        //insert the header
        $writer->insertOne($header);


        foreach($records as $record):

            //Fetch Learner
            $learners = json_decode($this->user->getUsers(array(
                'FirstName' => trim($record['FirstName']),
                'LastName' => trim($record['LastName']),
                'Role' => 1,
            ), true));

            if (!$learners[0]) {
                $result = array_map('trim', $record);

                echo $result['FirstName'] . ' ' . $record['LastName'].PHP_EOL;
                $writer->insertOne($result);
                $counter++;
            }

        endforeach;

        $return = array('status' => true, 'message' => "Learners missing: $counter");

    }

}
