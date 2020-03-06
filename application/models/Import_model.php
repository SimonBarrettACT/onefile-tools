<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Import_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();

    }

	public function getFile($file='learners') {
		
    // Set file properties
    $filename = "$file.csv";
    $local_path = APPPATH . 'imports\\';

    // Fetch records 
    $iteratorRecords = $this->csv->getRecords($local_path . $filename);
    $arrayRecords = iterator_to_array($iteratorRecords, true);

    return $arrayRecords;

	}
	


}
