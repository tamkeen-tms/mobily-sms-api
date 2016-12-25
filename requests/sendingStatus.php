<?php namespace MobilyAPI\requests;

	use MobilyAPI\Request;

	/**
	 * Checks sending status
	 * @package MobilyAPI\requests
	 */
	class SendingStatus extends Request{
		/**
		 * @var string
		 */
		public $function = 'sendStatus';
		/**
		 * @var array
		 */
		public $responseCodes = [
			1 => 'available'
		];
		/**
		 * @var array
		 */
		public $successResponseCode = [1];

		/**
		 * Tells if it's okay to send right now
		 * @return bool
		 */
		public function available(){
			return $this->getResponse()->raw() == 1;
		}
	}