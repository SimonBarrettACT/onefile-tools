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

        //Get all the learners from OneFile
        $json = $this->user->getUsers();
        $learners = json_decode($json, true);

        //Get all the assessors from OneFile
        $json = $this->user->getUsers(array('Role' => 5));
        $assessors = json_decode($json, true);


        //Fetch reviews for the last month
        $firstDay = new \DateTime('first day of last month 00:00:00');
        $lastDay = new \DateTime('last day of last month 23:59:59');

        $dateFrom = $firstDay->format(DateTime::ATOM); 
        $dateTo = $lastDay->format(DateTime::ATOM);

        $parameters = array();
        
        $parameters['Status'] = 1;
        $parameters['DateFrom'] = $dateFrom;
        $parameters['DateTo'] = $dateTo;

        //Get reviews
        $json = $this->review->getReviews($parameters);
        $reviews = json_decode($json, true);


        //Send or save report
        if ($reviews):
            $counter = count($reviews);

            //Sort reviews
            usort($reviews, "sort_scheduled_date");

            //Write to spreadsheet
            $inputFileName = FCPATH . "templates/review-audit-template.xlsx";
            $spreadsheet = IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();
            
            $row = 2;
            $reviewCounter = 0;
            foreach($reviews as $review):
                
                echo ".";
                
                $reviewCounter++;

                $fullReview = json_decode($this->review->getReview($review['ID']), true);
                if($reviewCounter >= 100) sleep(1);

                $reviewID = $fullReview['ID'];
                $userID = $fullReview['LearnerID'];

                //Only reviews that have an assessor and have been signed by the assessor
                if(isset($fullReview['AssessorID']) and isset($fullReview['AssessorSignedOn'])):
                    $assessorID = $fullReview['AssessorID'];

                    $learner = search_users_by_id($learners, $userID);
                    $assessor = search_users_by_id($assessors, $assessorID);

                    //Scheduled date
                    if(isset($fullReview['ScheduledFor'])):
                        $scheduledFor = date('d/m/Y', strtotime($fullReview['ScheduledFor']));
                    else:
                        $scheduledFor = '';
                    endif;

                    //Started date
                    if(isset($fullReview['StartedOn'])):
                        $startedOn = date('d/m/Y', strtotime($fullReview['StartedOn']));
                    else:
                        $startedOn = '';
                    endif;

                    if(isset($fullReview['AssessorSignedOn'])):
                        $assessorSigned = date('d/m/Y', strtotime($fullReview['AssessorSignedOn']));
                    else:
                        $assessorSigned = '';
                    endif;

                    if(isset($fullReview['LearnerSignedOn'])):
                        $learnerSigned = date('d/m/Y', strtotime($fullReview['LearnerSignedOn']));
                    else:
                        $learnerSigned = '';
                    endif;

                    //Check compliance
                    $compliant = '';
                    if(isset($fullReview['AssessorSignedOn']) and isset($fullReview['LearnerSignedOn'])):
                        $start_date = new DateTime($fullReview['AssessorSignedOn']);
                        $since_start = $start_date->diff(new DateTime($fullReview['LearnerSignedOn']));
                        $minutes = $since_start->days * 24 * 60;
                        $minutes += $since_start->h * 60;
                        $minutes += $since_start->i;
                        if($minutes > 30):
                            $compliant = 'No';
                        else:
                            $compliant = 'Yes';
                        endif;
                    endif;
                    //Learner not signed
                    if(isset($fullReview['AssessorSignedOn']) and !isset($fullReview['LearnerSignedOn'])):
                        $compliant = 'No';
                    endif;

                    $sheet->setCellValue('A'.$row, $reviewID);
                    $sheet->getCell('A'.$row)->getHyperlink()->setUrl("https://live.onefile.co.uk/review/review_form.aspx?UserID=$userID&ReviewID=$reviewID");                
                    $sheet->setCellValue('C'.$row, $learner['FirstName'] . ' ' . $learner['LastName'] );
                    $sheet->setCellValue('D'.$row, $assessor['FirstName'] . ' ' . $assessor['LastName']);
                    $sheet->setCellValue('G'.$row, $scheduledFor);
                    $sheet->setCellValue('H'.$row, $startedOn);
                    $sheet->setCellValue('I'.$row, $assessorSigned);
                    $sheet->setCellValue('J'.$row, $learnerSigned);
                    $sheet->setCellValue('K'.$row, $compliant);
                    ++$row;
                endif;

            endforeach;
        
            $sheet->setSelectedCell('A2');

            //Set filename
            //$excelFile = '/webroot/storage/reviews/Review-' . $firstDay->format('M-yy') . '.xlsx';

            $excelFile = FCPATH . 'output/Review-' . $firstDay->format('M-yy') . '.xlsx';


            $writer = new Xlsx($spreadsheet);
            $writer->save($excelFile);

            echo "Job completed. Reviews found: $counter".PHP_EOL;
            die();

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
                $this->mail->setFrom(env('MAIL_FROM_EMAIL'), env('MAIL_FROM_NAME'));

                //Set who the message is to be sent to
                //$this->mail->addAddress('jfrangoulis@cavc.ac.uk', 'Jacki Frangoulis');
                $this->mail->addAddress(env('MAIL_TO_EMAIL'), env('MAIL_TO_NAME'));

                $mail->addBCC(env('MAIL_BC_EMAIL'), env('MAIL_BC_NAME'));

                // Attachments
                $this->mail->addAttachment($excelFile);         // Add attachments

                // Content
                $this->mail->isHTML(true);                                  // Set email format to HTML
                $this->mail->Subject = 'Latest Digital Review Report';
                $this->mail->Body    = '<p>Please find the latest report attached.</p><p>All the reviews listed are for reviews completed last month.</p><p>Regards<br/>Simon</p>';
                $this->mail->AltBody = 'Please find the latest report attached. All the reviews listed are for reviews completed last month.';

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

    public function debug_get()
    {
        $return = array('status' => true, 'message' => "Version: 1");
        $this->set_response($return, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
    }
}
