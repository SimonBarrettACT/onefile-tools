<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;
use League\Csv\Writer;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

class Reviews extends REST_Controller {

    private $mail;

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

        // Instantiation and passing `true` enables exceptions
        $this->mail = new PHPMailer(true);

        //Check output folder exists
        // if (!is_dir('/webroot/storage/reviews/')):
        //     mkdir('/webroot/storage/reviews/', 0777, TRUE);  
        // endif;

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

        $parameters = [];
        
        $parameters['Status'] = 1;
        $parameters['DateFrom'] = $dateFrom;
        $parameters['DateTo'] = $dateTo;

        //Get reviews
        $json = $this->review->getReviews($parameters);
        $reviews = json_decode($json, true);
        $reviewsFound = [];
        $rateLimiter();

        $statusList = array('', 'Not Started','Started but not signed', 'Signed by Assessor', 'Signed by Assessor and Learner');

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

        $arrayData = [];
        $arrayData[] = array_keys($reviewsFound[0]);

        for ($x = 0; $x < count($reviewsFound); $x++):
            //Set the status text
            $reviewsFound[$x]['Status'] = '';
            if($reviewsFound[$x]['ScheduledFor'])       $reviewsFound[$x]['Status'] = $statusList[1];  
            if($reviewsFound[$x]['StartedOn'])          $reviewsFound[$x]['Status'] = $statusList[2]; 
            if($reviewsFound[$x]['AssessorSignedOn'])   $reviewsFound[$x]['Status'] = $statusList[3]; 
            if($reviewsFound[$x]['LearnerSignedOn'])    $reviewsFound[$x]['Status'] = $statusList[4]; 

            //Set array data
            $arrayData[] = array_values($reviewsFound[$x]);

        endfor;

        //Send or save report
        if ($reviews):
            $counter = count($reviews);

            // $spreadsheet->getActiveSheet()->setCellValue('E26', 'www.phpexcel.net');
            // $spreadsheet->getActiveSheet()->getCell('E26')->getHyperlink()->setUrl('https://www.example.com');

            //Write to spreadsheet
            $inputFileName = FCPATH . "templates/test-template.xlsx";
            $spreadsheet = IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();
            
            $row = 2;
            foreach($reviews as $review):
                $sheet->setCellValue('B'.$row, 'Alex Learner');
                $sheet->setCellValue('C'.$row, 'Ann Assessor');
                $sheet->setCellValue('F'.$row, '01/01/2020');
                ++$row;
            endforeach;
        

            //Set filename
            $excelFile = '/webroot/storage/reviews/Review-' . $firstDay->format('M-yy') . '.xlsx';

            $writer = new Xlsx($spreadsheet);
            $writer->save($excelFile);

            try {
                //Tell PHPMailer to use SMTP
                $this->mail->isSMTP();

                //Enable SMTP debugging
                // SMTP::DEBUG_OFF = off (for production use)
                // SMTP::DEBUG_CLIENT = client messages
                // SMTP::DEBUG_SERVER = client and server messages
                if(env('SMTP_DEBUG')):
                    $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
                else:
                    $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
                endif;

                //Set the hostname of the mail server
                $this->mail->Host = 'smtp.gmail.com';

                //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
                $this->mail->Port = 587;

                //Set the encryption mechanism to use - STARTTLS or SMTPS
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

                //Whether to use SMTP authentication
                $this->mail->SMTPAuth = true;

                //Username to use for SMTP authentication - use full email address for gmail
                $this->mail->Username = env('SMTP_USERNAME');

                //Password to use for SMTP authentication
                $this->mail->Password = env('SMTP_PASSWORD');

                //Set who the message is to be sent from
                $this->mail->setFrom('simonbarrett@acttraining.org.uk', 'Simon Barrett');

                //Set who the message is to be sent to
                $this->mail->addAddress('simonbarrett@me.com', 'Simon Barrett');

                // Attachments
                $this->mail->addAttachment($excelFile);         // Add attachments

                // Content
                $this->mail->isHTML(true);                                  // Set email format to HTML
                $this->mail->Subject = 'Latest Digital Review Report';
                $this->mail->Body    = 'Please find the latest report attached.';
                $this->mail->AltBody = 'Please find the latest report attached.';

                $this->mail->send();

                if(env('SMTP_DEBUG')) echo 'Email has been sent';
                $emailStatus = 'Email has been sent';

            } catch (Exception $e) {
                if(env('SMTP_DEBUG')) echo "Email could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
                $emailStatus = "Email could not be sent.";
            }

        else:
            $counter = 0;
        endif;


        $return = array('status' => true, 'message' => "Job completed. Reviews found: $counter", "email" => $emailStatus);
        $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code

    }    

}
