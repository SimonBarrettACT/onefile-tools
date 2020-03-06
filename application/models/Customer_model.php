<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;

class Customer_model extends CI_Model {

    var    $client;
    public $sessionKey;
    public $customerToken;
    public $organisationID;

    public function __construct()
    {
        parent::__construct();

        // Set onefile api credentials

        //Create Guzzle Client
		$this->client = new Client([
			// Base URI is used with relative requests
			'base_uri' => env('ONEFILE_BASE_URL')
        ]);
        
        // organisationID
        $this->organisationID = env('ONEFILE_ORGANISATION_ID');

        //api token
        $this->customerToken  = env('ONEFILE_API_KEY');

		//Authenticate
		$response = $this->client->request('POST', 'Authentication',
		['headers' => [
			'X-CustomerToken' => $this->customerToken,
			'Content-Type' => 'application/x-www-form-urlencoded'
			]]);
		$this->sessionKey = $response->getBody();

    }

	public function getCustomer() {
		//Request all Learners
		$response = $this->client->request('GET', "Customer",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			]
		]);

		//Return Customer
		return $response->getBody();
        
	} 

}
