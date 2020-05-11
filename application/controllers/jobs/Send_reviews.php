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

class Send_reviews extends REST_Controller {

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
        $this->load->model('review_model', 'review');

    }
    
    public function index_get()
    {
        //Start rate limiter
        $rateLimiter = ratelimiter();

        //Get all the learners from OneFile
        $json = $this->user->getUsers();
        $learners = json_decode($json, true);
        $rateLimiter();

        //Get all the assessors from OneFile
        $json = $this->user->getUsers(5);
        $assessors = json_decode($json, true);
        $rateLimiter();

        //Fetch reviews for the last month
        $firstDay = new \DateTime('first day of last month 00:00:00');
        $lastDay = new \DateTime('last day of last month 23:59:59');

        $dateFrom = $firstDay->format(DateTime::ATOM); 
        $dateTo = $lastDay->format(DateTime::ATOM);

        $parameters['Status'] = 1;
        $parameters['DateFrom'] = $dateFrom;
        $parameters['DateTo'] = $dateTo;

        //Get reviews
        $json = $this->review->getReviews($parameters);
        $reviews = json_decode($json, true);
        $reviewsFound = [];
        $rateLimiter();


        //Loop through the reviews and add to CSV
        if($reviews):
            foreach($reviews as $review):
                $json = $this->review->getReview($review['ID']);
                $found = json_decode($json, true);

                //Process to add missing fields
                $keys = array('ID','ScheduledFor','LearnerID','AssessorID','EmployerID','Status','CreatedOn','StartedOn','AssessorSignedOn','LearnerSignedOn','EmployerSignedOn','EndTime','VisitID','Progress');
                $values = [];
                foreach($keys as $key):
                    if(!isset($found[$key])):
                        $values[] = '';
                    else:
                        $values[] = $found[$key];
                    endif;
                endforeach;
                $found = array_combine($keys, $values);

                if($found) $reviewsFound = array_merge($reviewsFound, [$found]);
                $rateLimiter();
            endforeach;
        endif;

        //Send or save report
        if ($reviews):
            $counter = count($reviews);

            try {
                $header = array_keys($reviewsFound[0]);
    
                $writer = Writer::createFromPath(FCPATH.'output/file.csv', 'w+');
                //insert the header
                $writer->insertOne($header);
                //insert records
                $writer->insertAll($reviewsFound);
            } catch (CannotInsertRecord $e) {
                $e->getRecords(); //returns [1, 2, 3]
            }

        else:
            $counter = 0;
        endif;

        $return = array('status' => true, 'message' => "Job completed. Reviews found: $counter");
        $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }    

}
