<?php namespace MobilyAPI\requests;

	use MobilyAPI\Client;
	use MobilyAPI\Request;

	/**
	 * Deletes a scheduled sms by id
	 * @package MobilyAPI\requests
	 */
	class DeleteSMS extends Request{
		/**
		 * @var string
		 */
		public $function = 'deleteMsg';
		/**
		 * @var array
		 */
		public $responseCodes = [
			1 => 'deleted',
			2 => 'invalid_account_credentials',
			3 => 'invalid_account_credentials',
			4 => 'invalid_deletion_key',
		];

		/**
		 * Deletes a scheduled message through its id
		 * @param Client $client The client through which this request should be made
		 * @param array $deletionKey The message deletion id
		 */
		public function __construct(Client $client, $deletionKey){
			// The request
			parent::__construct($client, [
				'deleteKey' => $deletionKey
			]);
		}

		/**
		 * Tells if the message was deleted
		 * @return bool
		 */
		public function deleted(){
			return $this->getResponse()->raw() == '1';
		}
	}