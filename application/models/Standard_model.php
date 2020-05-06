<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;

class Standard_model extends CI_Model {

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

	public function getStandard($id=0) {
		
		try {
		//Request Learner
		$response = $this->client->request('GET', "Standard/$id",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => [
				'organisationID' => $this->organisationID
			]
		]);

		//Return Standard
		return $response->getBody();

		} catch (Exception $e) {
			return null;			
		}
        
	}
	
	public function getStandards($newParameters=[], $pageNumber=0, $pageSize=50) {

			//Set parameters
			$basicParameters = array(
				'organisationID' => $this->organisationID
			);
	
			$parameters = array_merge($basicParameters, $newParameters);

		try {

		If ($pageNumber > 0) {
			$url = "Standard/Search/$pageNumber/$pageSize";
		} else {
			$url = "Standard/Search";
		}

		//Request standards
		$response = $this->client->request('POST', $url,
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);



		//Return Standards
		return $response->getBody();


		} catch (Exception $e) {
			return null;			
		}
        
	}
	
	public function assignStandard($standardId, $learnerId) {
		
		//Set parameters
		$parameters = [
			'organisationID' => $this->organisationID
		];

		try {

		//assign standard
		$response = $this->client->request('POST', "Standard/$standardId/Assign/$learnerId",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);

		//Return response
		return true;

		} catch (Exception $e) {
			return false;			
		}

	}

}
