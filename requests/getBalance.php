<?php namespace MobilyAPI\requests;

	use MobilyAPI\Request;

	/**
	 * A request to check for the current balance
	 * @package MobilyAPI
	 */
	class GetBalance extends Request{
		/**
		 * @var string
		 */
		public $function = 'balance';
		/**
		 * @var array
		 */
		public $responseCodes = [
			0 => 'connection_failed',
			1 => 'invalid_username',
			2 => 'invalid_password'
		];

		/**
		 * The balance as an array
		 * @return array|bool
		 */
		public function balance(){
			// The response
			$response = $this->getResponse();

			// The body
			$balance = explode('/', $response->raw());

			if(count($balance) != 2){
				return false;
			}

			return [
				'remaining' => $balance[1],
				'total' => $balance[0]
			];
		}

		/**
		 * The remaining balance
		 * @return int|null
		 */
		public function remaining(){
			$balance = $this->balance();

			if(is_array($balance)){
				return $balance['remaining'];

			}else{
				return false;
			}
		}

		/**
		 * The total balance
		 * @return int|false
		 */
		public function total(){
			$balance = $this->balance();

			if(is_array($balance)){
				return $balance['total'];

			}else{
				return false;
			}
		}

		/**
		 * Tells if there is credit available
		 * @return bool
		 */
		public function available(){
			return (int) $this->remaining() > 0;
		}
	}
