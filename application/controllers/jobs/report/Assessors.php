<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;
use League\Csv\Writer;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include APPPATH . 'third_party/Filters.php';

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . '/libraries/REST_Controller.php';

/**
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */

class Assessors extends REST_Controller {

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

        //Set local path
        $local_path = APPPATH . '/tmp/';
        
        //Load the allusers.csv from ftp
        //and Filter all but assessors
        $filename="alluser.csv";
        $this->ftps->connection->download($filename, $local_path);
        $assessorIterator = new MyIterator_Filter_Assessors(
			$this->csv->getRecords($local_path . $filename)
		);   

        //Load users.csv (Learners)
        $filename="user.csv";
        $this->ftps->connection->download($filename, $local_path);
        $learnerIterator = new MyIterator_Filter_Archived(
			$this->csv->getRecords($local_path . $filename)
        );   
 
        //Load qsa.csv
        $filename="qsa.csv";
        $qsaIterator = $this->csv->getRecords(APPPATH . '/imports/' . $filename);  

        //Load the reviews.csv
        //and filter since 01/01/2020
        $filename="review.csv";
        $this->ftps->connection->download($filename, $local_path);
        //Set the date e.g. 2020-05-17 15:49:12
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2020-04-23 00:00:00');
        $reviewIterator = new MyIterator_Filter_Date(
			$this->csv->getRecords($local_path . $filename), $startDate
        );  

        //Load assessment.csv
        //and filter since 01/01/2020
        $filename="assessment.csv";
        $this->ftps->connection->download($filename, $local_path);
        //Set the date e.g. 2020-05-17 15:49:12
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $assessmentIterator = new MyIterator_Filter_Date(
			$this->csv->getRecords($local_path . $filename), $startDate
        );  

        $assessors = iterator_to_array($assessorIterator, true);
        $qsa = iterator_to_array($qsaIterator, true); 

        $header = ['ID', 'FirstName', 'LastName', 'DateLastLoggedIn', 'Learners', 'Assessments', 'Reviews', 'QSA'];
        $filename = 'assessors.csv';
        $writer = Writer::createFromPath($local_path . $filename, 'w');

        //insert the header
        $writer->insertOne($header);

        //Loop through assessors and create a record for each containing
        //UserID,FirstName,LastName,DateLastLoggedIn,Learners,Assessments,Reviews
        foreach($assessors as $assessor):

            //Count learners
            $learnersFound = new MyIterator_Filter_Assessor_By_Name(
                $learnerIterator, $assessor['FirstName'] . ' ' . $assessor['LastName']
            );

            //Count assessments
            $assessmentsFound = new MyIterator_Filter_Assessor_By_Name(
                $assessmentIterator, $assessor['FirstName'] . ' ' . $assessor['LastName']
            );

            //Count reviews
            $reviewsFound = new MyIterator_Filter_Assessor_By_Name(
                $reviewIterator, $assessor['FirstName'] . ' ' . $assessor['LastName']
            );

            $keyFound = search_users($qsa, false, $assessor['FirstName'], 'FirstName', $assessor['LastName'], 'LastName');

            $qsaMember = ($keyFound ? 'Yes' : 'No');
            $record = [$assessor['UserID'], $assessor['FirstName'], $assessor['LastName'], $assessor['DateLastLoggedIn'], count(iterator_to_array($learnersFound)), count(iterator_to_array($assessmentsFound)), count(iterator_to_array($reviewsFound)), $qsaMember];
            $writer->insertOne($record);

        endforeach;

        $return = array('status' => true, 'message' => "Job completed.");
        $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

    }    

}
