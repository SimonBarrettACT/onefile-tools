<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;
use League\Csv\Writer;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include APPPATH . 'third_party/Filters.php';

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH . 'libraries/REST_Controller.php';

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
            $this->response(array(
                'status' => FALSE,
                'error' => 'You do not have authorization to access this Job.'
            ), REST_Controller::HTTP_UNAUTHORIZED); // NOT_FOUND (404) being the HTTP response code
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
        //$this->ftps->connection->download($filename, $local_path);
        echo "All users loaded.".PHP_EOL;
        $assessorIterator = new MyIterator_Filter_Assessors(
			$this->csv->getRecords($local_path . $filename)
		);

        //Load users.csv (Learners)
        $filename="user.csv";
        //$this->ftps->connection->download($filename, $local_path);
        echo "Users loaded.".PHP_EOL;
        $learnerIterator = new MyIterator_Filter_Archived(
			$this->csv->getRecords($local_path . $filename)
        );

        //Load the reviews.csv
        //and filter since 01/01/2020
        $filename="review.csv";
        //$this->ftps->connection->download($filename, $local_path);
        //Set the date e.g. 2020-05-17 15:49:12
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2020-04-23 00:00:00');
        $reviewIterator = new MyIterator_Filter_Date(
			$this->csv->getRecords($local_path . $filename), $startDate
        );

        echo "Reviews loaded.".PHP_EOL;

        //Load assessment.csv
        //and filter since 01/01/2020
        $filename="assessment.csv";
        //$this->ftps->connection->download($filename, $local_path);
        //Set the date e.g. 2020-05-17 15:49:12
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $assessmentIterator = new MyIterator_Filter_Date(
			$this->csv->getRecords($local_path . $filename), $startDate
        );

        echo "Assessments loaded.".PHP_EOL;

        //Load actionplan1.csv
        //and filter since 01/01/2020
        $filename="actionplan1.csv";
        //$this->ftps->connection->download($filename, $local_path);
        //Set the date e.g. 2020-05-17 15:49:12
        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', '2020-01-01 00:00:00');
        $actionPlanIterator = new MyIterator_Filter_Date(
			$this->csv->getRecords($local_path . $filename), $startDate
        );

        echo "Action plans loaded.".PHP_EOL;

        $assessors = iterator_to_array($assessorIterator, true);

        $header = array('ID', 'FirstName', 'LastName', 'DateLastLoggedIn', 'Learners', 'TLAPs', 'Assessments', 'Reviews', 'MISID');
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

            //Count action plans
            $actionPlansFound = new MyIterator_Filter_Assessor_By_Name(
                $actionPlanIterator, $assessor['FirstName'] . ' ' . $assessor['LastName']
            );

            //Count assessments
            $assessmentsFound = new MyIterator_Filter_Assessor_By_Name(
                $assessmentIterator, $assessor['FirstName'] . ' ' . $assessor['LastName']
            );

            //Count reviews
            $reviewsFound = new MyIterator_Filter_Assessor_By_Name(
                $reviewIterator, $assessor['FirstName'] . ' ' . $assessor['LastName']
            );

            if(isset($assessor['MISReference'])):
                $misid = $assessor['MISReference'];
            else:
                $misid = '';
            endif;

            //Check for Maytas ID
            $re = '/A([0-9]{4})-([0-9]{4})-([0-9]{6})/m';
            if(!preg_match($re, $misid)) {$misid = '';}

            //if ($misid === ''):
                $record = [$assessor['UserID'], $assessor['FirstName'], $assessor['LastName'], $assessor['DateLastLoggedIn'], count(iterator_to_array($learnersFound)), count(iterator_to_array($actionPlansFound)),count(iterator_to_array($assessmentsFound)), count(iterator_to_array($reviewsFound)), $misid];
                $writer->insertOne($record);
            //endif;

            echo '.';

        endforeach;

        echo PHP_EOL;

//        try {
//            //Tell PHPMailer to use SMTP
//            $this->mail->isSMTP();
//
//            //Enable SMTP debugging
//            // SMTP::DEBUG_OFF = off (for production use)
//            // SMTP::DEBUG_CLIENT = client messages
//            // SMTP::DEBUG_SERVER = client and server messages
//            if(env('SMTP_DEBUG')):
//                $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
//            else:
//                $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
//            endif;
//
//            //Set the hostname of the mail server
//            $this->mail->Host = 'smtp.gmail.com';
//
//            //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
//            $this->mail->Port = 587;
//
//            //Set the encryption mechanism to use - STARTTLS or SMTPS
//            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//
//            //Whether to use SMTP authentication
//            $this->mail->SMTPAuth = true;
//
//            //Username to use for SMTP authentication - use full email address for gmail
//            $this->mail->Username = env('SMTP_USERNAME');
//
//            //Password to use for SMTP authentication
//            $this->mail->Password = env('SMTP_PASSWORD');
//
//            //Set who the message is to be sent from
//            $this->mail->setFrom(env('MAIL_FROM_EMAIL'), env('MAIL_FROM_NAME'));
//
//            //Set who the message is to be sent to
//            //$this->mail->addAddress('jfrangoulis@cavc.ac.uk', 'Jacki Frangoulis');
//            $this->mail->addAddress(env('MAIL_BC_EMAIL'), env('MAIL_BC_NAME'));
//
//            // Attachments
//            $this->mail->addAttachment($local_path . $filename);         // Add attachments
//
//            // Content
//            $this->mail->isHTML(true);                                  // Set email format to HTML
//            $this->mail->Subject = 'Assessors Report';
//            $this->mail->Body    = '<p>Please find the latest assessor report attached.</p><p>Regards<br/>Simon</p>';
//            $this->mail->AltBody = 'Please find the latest assessor report attached.';
//
//            $this->mail->send();
//
//            if(env('SMTP_DEBUG')) echo 'Email has been sent';
//            $emailStatus = 'Email has been sent';
//
//        } catch (Exception $e) {
//            if(env('SMTP_DEBUG')) echo "Email could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
//            $emailStatus = "Email could not be sent.";
//        }

        $emailStatus = '';

        $return = array('status' => true, 'message' => "Job completed. " . $emailStatus);
        $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

    }    

}
