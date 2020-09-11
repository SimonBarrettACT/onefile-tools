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

        // Set file properties
        $observers = 'assign_employers.csv';
        $local_path = APPPATH . '/imports/';

        // Fetch records
        $iteratorRecords = $this->csv->getRecords($local_path . $observers);
        $records = iterator_to_array($iteratorRecords, true);

        //Set counter
        $counter = 0;
        $failed = 0;
        $employer = null;

        $temp = 0;


        foreach($records as $record):

            //Fetch Learner
            $learners = json_decode($this->user->getUsers(array(
                'FirstName' => trim($record['FirstName']),
                'LastName' => trim($record['LastName']),
                'Role' => 1,
            )), true);

            sleep(1);

            if ($learners) {

                try {
                    //Find the employer
                    $employers = json_decode($this->user->getUsers(array(
                        'FirstName' => trim($record['EmployerFirst']),
                        'LastName' => trim($record['EmployerLast']),
                        'Email' => trim($record['EmployerEmail']),
                        'Role' => 40,
                    )), true);

                    if ($employers) {

                        $this->user->updateUser($learners[0]['ID'], array('DefaultEmployerID' => $employers[0]['ID']));

                        echo 'Assigned: ' . $record['FirstName'] . ' ' . $record['LastName'] . PHP_EOL;
                        echo '============================================'.PHP_EOL;
                        ++$counter;
                    }else {
                        echo 'Failed: ' . $record['FirstName'] . ' ' . $record['LastName'] . PHP_EOL;
                        echo '============================================'.PHP_EOL;
                        ++$failed;
                    }

                    sleep(1);

                } catch (Exception $e) {
                    echo 'Error Assigning ' . $record['FirstName'] . ' ' . $record['LastName'] . PHP_EOL;
                    echo $e->getMessage().PHP_EOL;
                    echo '============================================'.PHP_EOL;
                    ++$failed;
                    sleep(1);
                }

            }

        endforeach;

        $return = array('status' => true, 'message' => "Job completed. Accounts assigned: $counter Job completed. Accounts failed: $failed");
        $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }

}
