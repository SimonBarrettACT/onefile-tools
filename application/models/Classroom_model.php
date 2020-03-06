<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;

class Classroom_model extends CI_Model {

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

	public function getClassroom($id=0) {
		
		//Request Learner
		$response = $this->client->request('GET', "Classroom/$id",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => [
				'organisationID' => $this->organisationID
			]
		]);

		//Return Classroom
		return $response->getBody();
        
	}
	
	public function getClassrooms($role=1) {
		
		//Set parameters
		$parameters = [
			'role' => $role,
			'organisationID' => $this->organisationID
		];

		//Request Learner
		$response = $this->client->request('POST', "Classroom/Search",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);

		//Return Classrooms
		return $response->getBody();
        
	}
	
	public function createClassroom($newParameters) {
		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		$parameters = array_merge($basicParameters, $newParameters);

		//Request Learner
		$response = $this->client->request('POST', "Classroom",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);

		//Return response
		return $response->getBody();

	}

  	public function updateClassroom($id, $updatedParameters) {
		
		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		$parameters = array_merge($basicParameters, $updatedParameters);

		//Request Learner
		$response = $this->client->request('POST', "Classroom/$id",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);

		//Return Classroom
		return $response->getBody();
    
  }


}
