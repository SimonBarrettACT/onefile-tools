<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;

class Provider_model extends CI_Model {

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

	public function getProvider($id=0) {
		
		try {
		//Request Learner
		$response = $this->client->request('GET', "Provider/$id",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => [
				'organisationID' => $this->organisationID
			]
		]);

		//Return Provider
		return $response->getBody();

		} catch (Exception $e) {
			return null;			
		}
        
	}
	
	public function getProviders($newParameters=[], $pageNumber=0, $pageSize=50) {

			//Set parameters
			$basicParameters = [
				'organisationID' => $this->organisationID
			];
	
			$parameters = array_merge($basicParameters, $newParameters);

		try {

		If ($pageNumber > 0) {
			$url = "Provider/Search/$pageNumber/$pageSize";
		} else {
			$url = "Provider/Search";
		}

		//Request Providers
		$response = $this->client->request('POST', $url,
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);

		//Return Providers
		return $response->getBody();


		} catch (Exception $e) {
			return null;			
		}
        
	}
	
	public function createProvider($newParameters) {
		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		$parameters = array_merge($basicParameters, $newParameters);

		try {

		//Create Provider
		$response = $this->client->request('POST', "Provider",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $parameters
		]);

		//Return response
		return $response->getBody();

		} catch (Exception $e) {
			return null;			
		}

	}

  	public function updateProvider($id, $updatedParameters) {
		
		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		$parameters = array_merge($basicParameters, $updatedParameters);

		try {
			//Update Provider
			$response = $this->client->request('POST', "Provider/$id",
			[
				'headers' => [
				'X-TokenID' => strval($this->sessionKey),
				'Content-Type' => 'application/x-www-form-urlencoded'
				],
				'form_params' => $parameters
			]);
			
			//Return success
			return true;
	
		} catch (Exception $e) {
			return false;			
		}
		
  }


}
