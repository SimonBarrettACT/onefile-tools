<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;

class Placement_model extends CI_Model {

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

	public function getPlacement($id=0) {
		
		//Request Learner
		$response = $this->client->request('GET', "Placement/$id",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => [
				'organisationID' => $this->organisationID
			]
		]);

		//Return Placement
		return $response->getBody();
        
	}
	
	public function getPlacements() {
		
		//Set parameters
		$parameters = [
			'organisationID' => $this->organisationID
		];

		//Request Learner
		$response = $this->client->request('POST', "Placement/Search",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);

		//Return Placements
		return $response->getBody();
        
	}
	
	public function createPlacement($newParameters) {
		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		$parameters = array_merge($basicParameters, $newParameters);

		//Request Learner
		$response = $this->client->request('POST', "Placement",
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

  	public function updatePlacement($id, $updatedParameters) {
		
		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		$parameters = array_merge($basicParameters, $updatedParameters);

		//Request Learner
		$response = $this->client->request('POST', "Placement/$id",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);

		//Return Placement
		return $response->getBody();
    
  }


}
