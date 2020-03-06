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
			return '{"ID": "' . $id . '", "Message": "The placement was not found."}';			
		}
        
	} 

	public function getPlacements() {
		
		try {
		//Request all placements
		$response = $this->client->request('POST', "Placement/Search",
		[
			'headers' => [
			'X-TokenID' => strval($this->sessionKey),
			'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => [
				'OrganisationID' => $this->organisationID
			]
		]);

		//Return Placements
		return $response->getBody();

			} catch (Exception $e) {
				return '{"Message": "No placements were found."}';
			}
		  
	} 

}
