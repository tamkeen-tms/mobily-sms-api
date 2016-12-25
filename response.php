<?php namespace MobilyAPI;

	/**
	 * The response to an API request
	 *
	 * @package MobilyAPI
	 */
	class Response{
		/**
		 * The client
		 * @var Client
		 */
		public $client;
		/**
		 * The response object
		 * @var \GuzzleHttp\Psr7\Response
		 */
		public $response;
		/**
		 * The request itself
		 * @var Request
		 */
		public $request;

		/**
		 * @param Client $client
		 * @param Request $request
		 * @param \GuzzleHttp\Psr7\Response $response
		 */
		public function __construct(Client $client, Request $request, \GuzzleHttp\Psr7\Response $response){
			$this->client = $client;
			$this->request = $request;
			$this->response = $response;
		}

		/**
		 * Guzzle HTTP response object
		 * @return \GuzzleHttp\Psr7\Response
		 */
		public function object(){
			return $this->response;
		}

		/**
		 * The raw body of the response
		 * @return string
		 */
		public function raw(){
			return (string) $this->object()->getBody();
		}

		/**
		 * The result of the request. Each API "function" has its own set of response codes, go through the function
		 * class for the possible results
		 *
		 * @return string
		 */
		public function result(){
			if(!$this->request->successful()){
				return 'request_failed';
			}

			// Response code
			$response = $this->raw();

			return array_key_exists($response, $this->request->responseCodes)
				?$this->request->responseCodes[$response] :$response;
		}
	}