<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use GuzzleHttp\Client;

class Organisation_model extends CI_Model {

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

	public function getOrganisation($id) {
		//Request Organisation
		$response = $this->client->request('GET', "Organisation/$id",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			]
		]);

		//Return Organisation
		return $response->getBody();
        
	} 

	public function getOrganisations($newParameters=[], $pageNumber=0, $pageSize=50) {

		//Set parameters
		$basicParameters = [
			'organisationID' => $this->organisationID
		];

		$parameters = array_merge($basicParameters, $newParameters);

	try {

	If ($pageNumber > 0) {
		$url = "Organisation/Search/$pageNumber/$pageSize";
	} else {
		$url = "Organisation/Search";
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

	//Return Classrooms
	return $response->getBody();


	} catch (Exception $e) {
		return null;			
	}
			
}

public function getLearningAimStatuses($organisationId) {
	//Request Organisation
	$response = $this->client->request('GET', "Organisation/$organisationId/LearningAimStatuses",
	[
		'headers' => [
		'X-TokenID' => strval($this->sessionKey),
		'Content-Type' => 'application/x-www-form-urlencoded'
		]
	]);

	//Return LearningAimStatuses
	return $response->getBody();
			
} 

public function getLearnerStatuses($organisationId) {
	//Request Organisation
	$response = $this->client->request('GET', "Organisation/$organisationId/LearnerStatuses",
	[
		'headers' => [
		'X-TokenID' => strval($this->sessionKey),
		'Content-Type' => 'application/x-www-form-urlencoded'
		]
	]);

	//Return LearnerStatuses
	return $response->getBody();
			
} 

public function getAssignedStandards($organisationId) {
	//Request Organisation
	$response = $this->client->request('GET', "Organisation/$organisationId/AssignedStandards",
	[
		'headers' => [
		'X-TokenID' => strval($this->sessionKey),
		'Content-Type' => 'application/x-www-form-urlencoded'
		]
	]);

	//Return AssignedStandards
	return $response->getBody();
			
} 

}
