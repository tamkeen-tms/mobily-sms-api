<?php namespace MobilyAPI;

	/**
	 * Mobily SMS service API client
	 * @package MobilyAPI
	 */
	class Client{
		/**
		 * The setup
		 * @var array
		 */
		public $setup = [
			'username' => null,
			'password' => null,
			'base_uri' => 'http://www.mobily.ws/api/',
			'request_time_out' => 5,
			'verify_ssl_certificate' => false,
			'debug' => false
		];
		/**
		 * The default values for request params
		 * @var array
		 */
		public $paramDefaults = [
			'sender' => null,
			'domainName' => null,
			'applicationType' => '68',
			'lang' => 3
		];

		/**
		 * @param $mobileNumber The account mobile number (Mobily uses the mobile phone number as the username)
		 * @param $password The account password
		 * @param array $paramDefaults [optional] Request default params overrides
		 * @param array $setup [optional] Setup overrides
		 */
		public function __construct($mobileNumber, $password, array $paramDefaults = [], array $setup = []){
			// The account
			$this->setup['username'] = $mobileNumber;
			$this->setup['password'] = $password;

			// The client setup
			$this->setup = array_merge($this->setup, $setup);

			// The request defaults params
			$this->paramDefaults = array_merge($this->paramDefaults, $paramDefaults);

			// Set a default "domain name"
			if(empty($this->paramDefaults['domainName'])){
				$this->paramDefaults['domainName'] = $_SERVER['SERVER_NAME'];
			}
		}

		/**
		 * Create a new request instance
		 *
		 * @param string $function The targeted function, e.g. "sendSMS" or "sendingStatus"
		 * @param array $params [optional] The request params
 		 *
		 * @return Request
		 * @throws \Exception
		 */
		public function createRequest($function, array $params = []){
			// The request class name
			$requestClass = "MobilyAPI\\requests\\$function";

			// Check if the name isn't valid
			if(!class_exists($requestClass)){
				throw new \Exception('Invalid request name: ' . basename($requestClass));
			}

			// Prepend the client to the params
			array_unshift($params, $this);

			// Call the request
			return new $requestClass(...$params);
		}

		/**
		 * Makes a request and directly tells if it was successful
		 * @param $function
		 * @param array $params
		 *
		 * @return Response
		 */
		public function request($function, array $params = []){
			// Make the request
			$request = $this->createRequest($function, $params);

			// Send the request
			$response = $request->send();

			return $response;
		}
	}
