<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use League\Csv\Writer;
use League\Csv\Reader;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;


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

class Employers extends REST_Controller {

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

    }

    public function index_get()
    {

        $currentAssessor = '';

        $template = APPPATH . "/imports/Employer Details (Template).xlsx";

        //Load the data_for_employers.csv
        $filename= APPPATH . "/imports/data_for_employers.csv";

        $reader = Reader::createFromPath($filename, 'r');
        $records = $reader->getRecords();
        $header = array(
            'FirstName',
            'LastName',
            'DOB',
            'ExpectedEnd',
            'MIS',
            'AssessorFirst',
            'AssessorLast',
            'EmployerFirst',
            'EmployerLast',
            'EmployerEmail',
            'Workplace'
        );

        $assessorsLearners = [];

        foreach ($records as $offset => $record) {
            if ($offset > 0):
                if ($currentAssessor !== $record[4]):

                    if ($currentAssessor !== ''):
//                        $csvFile = FCPATH . 'output/assessors/Employer Details [' . $currentAssessor . '].csv';
//                        $writer = Writer::createFromPath($csvFile, 'w+');
//                        $writer->insertOne($header);
//                        $writer->insertAll($assessorsLearners); //using an array

                        //Write to Excel
                        $spreadsheet = IOFactory::load($template);
                        $spreadsheet->getActiveSheet()->fromArray($assessorsLearners, NULL, 'A6');
                        $xlsxFile = FCPATH . 'output/assessors/Employer Details [' . $currentAssessor . '].xlsx';
                        $writer = IOFactory::createWriter($spreadsheet, "Xlsx");
                        $writer->save($xlsxFile);

                        $spreadsheet->disconnectWorksheets();
                        unset($spreadsheet);

                    endif;


                    //Start a new file
                    $assessorsLearners = [];
                endif;

                $learnerSplitName = explode(", ", $record[0]);
                $assessorSplitName = explode(" ", $record[4]);
                $learner = array(
                    $learnerSplitName [1],
                    $learnerSplitName [0],
                    $record[1],
                    $record[2],
                    $record[3],
                    $assessorSplitName[0],
                    $assessorSplitName[1]
                );

                $assessorsLearners[] = $learner;
                $currentAssessor = $record[4];

            endif;
        }



    }

}
