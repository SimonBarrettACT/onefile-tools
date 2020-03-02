<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use League\Csv\Reader;

class Csv {

        public function readRawFile($filename)
        {
            //load the CSV document from a file path
            $csv = Reader::createFromPath($filename, 'r');
            $csv->setHeaderOffset(0);

            return $csv->getContent(); //returns the CSV document as a string
        }

        public function getRecords($filename)
        {
            //load the CSV document from a file path
            $csv = Reader::createFromPath($filename, 'r');
            $csv->setHeaderOffset(0);

            return $csv->getRecords(); //returns all the CSV records as an Iterator object
        }

        public function getHeader($filename)
        {
            //load the CSV document from a file path
            $csv = Reader::createFromPath($filename, 'r');
            $csv->setHeaderOffset(0);

            return $csv->getHeader(); //returns the CSV header record
        }

}
