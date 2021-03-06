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

	public function getPlacement($id) {
		try {
		//Request a placement by id
		$response = $this->client->request('GET', "Placement/$id",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			]
		]);

		//Return Placement
		return $response->getBody();
		
		} catch (Exception $e) {
			return null;			
		}
        
	} 

	public function getPlacements($newParameters=[], $pageNumber=0, $pageSize=50) {

		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		$parameters = array_merge($basicParameters, $newParameters);

	try {

	If ($pageNumber > 0) {
		$url = "Placement/Search/$pageNumber/$pageSize";
	} else {
		$url = "Placement/Search";
	}

	//Request placements
	$response = $this->client->request('POST', $url,
	[
		'headers' => [
		'X-TokenID' => strval($this->sessionKey),
		'Content-Type' => 'application/x-www-form-urlencoded'
		],
		'form_params' => $parameters
	]);

	//Return Placements
	return $response->getBody();


	} catch (Exception $e) {
		return null;			
	}
			
}

public function createPlacement($newParameters) {
	//Set parameters
	$basicParameters = [
		'organisationID' => $this->organisationID
	];

	$parameters = array_merge($basicParameters, $newParameters);

	try {

	//Create Placement
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

	} catch (Exception $e) {
		return null;			
	}

}

public function updatePlacement($id, $updatedParameters) {
		
	//Set parameters
	$basicParameters = [
		'organisationID' => $this->organisationID
	];

	$parameters = array_merge($basicParameters, $updatedParameters);

	try {
		//Update Placement
		$response = $this->client->request('POST', "Placement/$id",
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