<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;

class User_model extends CI_Model {

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

	public function getUser($id=0) {
		
		//Request Learner
		$response = $this->client->request('GET', "User/$id",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => [
				'organisationID' => $this->organisationID
			]
		]);

		//Return User
		return $response->getBody();
        
	}
	
	public function getUsers($newParameters=array('Role' => 1), $pageNumber=0, $pageSize=50) {

			//Set parameters
			$basicParameters = [
				'organisationID' => $this->organisationID
			];

			$parameters = array_merge($basicParameters, $newParameters);

		try {

		If ($pageNumber > 0) {
			$url = "User/Search/$pageNumber/$pageSize";
		} else {
			$url = "User/Search";
		}

		//Request classrooms
		$response = $this->client->request('POST', $url,
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);

		//Return Users
		return $response->getBody();


		} catch (Exception $e) {
			return null;			
		}
				
	}	
	
	public function createUser($newParameters) {
		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		$parameters = array_merge($basicParameters, $newParameters);

		//Request Learner
		$response = $this->client->request('POST', "User",
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

  	public function updateUser($id, $updatedParameters) {
		
		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		$parameters = array_merge($basicParameters, $updatedParameters);

		//Request Learner
		$response = $this->client->request('POST', "User/$id",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);

		//Return User
		return $response->getBody();
    
  }

	public function deleteUser($id) {

			//Set parameters
			$basicParameters = [
				'organisationID' => $this->organisationID
			];

			//Delete Learner
			$response = $this->client->request('DELETE', "User/$id",
			[
				'headers' => [
				'X-TokenID' => strval($this->sessionKey),
				'Content-Type' => 'application/x-www-form-urlencoded'
				],
				'form_params' => $basicParameters
			]);

	}

	public function archiveUser($id) {

		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		//Archive User
		$response = $this->client->request('POST', "User/$id/Archive",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $basicParameters
		]);

}

	//Assign a user to a learner
	public function assignUser($assignId, $assignParameters = []) {
		
		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		$parameters = array_merge($basicParameters, $assignParameters);

		//Assign User
		$response = $this->client->request('POST', "User/$assignId/Assign",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);

		return $response->getBody();

	}

	public function getUserUnitSummary($id) {
		
		//Request Learner
		$response = $this->client->request('GET', "User/$id/UnitSummary",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => [
				'organisationID' => $this->organisationID
			]
		]);

		//Return User Unit Summary
		return $response->getBody();
        
	}

}
