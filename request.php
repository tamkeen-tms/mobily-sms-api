<?php namespace MobilyAPI;

	use GuzzleHttp\Client as Guzzle;

	/**
	 * New request to the API
	 *
	 * @package MobilyAPI
	 */
	class Request{
		/**
		 * The request client
		 * @var Client
		 */
		public $client;
		/**
		 * The request client
		 * @var Guzzle
		 */
		public $request;
		/**
		 * The response
		 * @var Response
		 */
		public $response;

		/**
		 * The targeted function in the API
		 * @var String
		 */
		public $function;
		/**
		 * The request parameters
		 * @var Array
		 */
		public $params;
		/**
		 * The response codes for the function
		 * @var array
		 */
		public $responseCodes = [];

		/**
		 * @param \MobilyAPI\Client $client The client through which the request will be made
		 * @param array $params The request params
		 */
		public function __construct(Client $client, array $params = []){
			// The API client
			$this->client = $client;

			// The setup
			$setup = $this->client->setup;

			// Create the client
			$this->request = new Guzzle([
				'base_uri' => $setup['base_uri'],
				'connect_timeout' => $setup['request_time_out'],
				'debug' => $setup['debug'],
				'verify' => $setup['verify_ssl_certificate']
			]);

			// The request params
			$this->params = $params;
		}

		/**
		 * Send the request to the API
		 * @return Response
		 */
		public function send(){
			// The request params
			$params = array_merge($this->client->paramDefaults, $this->params);

			// Add the account credentials
			$params['mobile'] = $this->client->setup['username'];
			$params['password'] = $this->client->setup['password'];

			// Make the request
			$response = $this->object()->request('POST', $this->function . '.php', [
				'form_params' => $params
			]);

			// Return the response
			return $this->response = new Response($this->client, $this, $response);
		}

		/**
		 * The request client
		 * @return Client
		 */
		public function getClient(){
			return $this->client;
		}

		/**
		 * Returns the request object
		 * @return Guzzle
		 */
		public function object(){
			return $this->request;
		}

		/**
		 * Returns the params used in the request
		 * @return Array
		 */
		public function getParams(){
			return $this->params;
		}

		/**
		 * The response
		 * @return Response
		 */
		public function getResponse(){
			if(empty($this->response)){
				$this->send();
			}

			return $this->response;
		}

		/**
		 * Tells if the request was successful
		 * @return bool
		 */
		public function successful(){
			// Get the response
			$response = $this->getResponse();

			return ( $response->object()->getStatusCode() == 200 && !empty($response->raw()) );

			return true;
		}

		/**
		 * Tells if the sending has failed
		 * @return bool
		 */
		public function failed(){
			return !$this->successful();
		}
	}