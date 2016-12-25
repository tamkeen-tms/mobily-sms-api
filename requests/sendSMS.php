<?php namespace MobilyAPI\requests;

	use Carbon\Carbon;
	use MobilyAPI\Client;
	use MobilyAPI\Request;

	/**
	 * Sends a new SMS message
	 *
	 * @package requests
	 */
	class SendSMS extends Request{
		/**
		 * The function
		 * @var string
		 */
		public $function = 'msgSend';
		/**
		 * The response codes
		 * @var array
		 */
		public $responseCodes = [
			1 => 'message_sent',
			2 => 'no_credit',
			3 => 'not_enough_credit',
			4 => 'invalid_account_credentials',
			5 => 'invalid_account_credentials',
			6 => 'service_down',
			10 => 'invalid_variable_sets_count',
			13 => 'invalid_sender_name',
			14 => 'inactive_sender_name',
			15 => 'incorrect_phone_numbers',
			16 => 'missing_sender_name',
			17 => 'invalid_message_or_bad_encoding',
			18 => 'sending_denied_by_support',
			19 => 'missing_application_type'
		];
		/**
		 * The time the message was scheduled to be sent in
		 * @var Carbon
		 */
		public $time;

		/**
		 * @param Client $client The client through which the message will be sent
		 * @param array $numbers Array of the targeted numbers
		 * @param $message The message body, in UTF-8 encoding
		 * @param null|Carbon $time [optional] The time at which this message should be schedule for sending
		 * @param array $variableSets [optional] In case the message has different values for each recipient
		 *                            (variables), you will need to pass an array of the value set for each recipient.
		 * @param string $messageId [optional] An id for the message. Read more on this in mobily official docs.
		 * @param string $sender      [optional] A sender name for the message. If not provided the default sender name
		 *                            will be used (which you can set for each \Client).
		 * @param string $deletionKey [optional] An id to use in case you wanted to delete the message you sent.
		 */
		public function __construct(Client $client, array $numbers, $message, $time = null, array $variableSets = [],
			$messageId = null, $sender = null, $deletionKey = null){

			// The numbers
			$numbers = array_unique($numbers);

			// A unique id for the message, as an id and a deletion key
			$id = uniqid();

			// Scheduled ?
			if($time){
				$this->time = Carbon::parse($time);
			}

			$params = [
				'numbers' =>    implode(',', $numbers),
				'msg' =>        $message,
				'sender' =>     $sender ?: $client->paramDefaults['sender'],
				'msgId' =>      $messageId ?: $id,
				'timeSend' =>   $this->time ?$this->time->format('h:m:s') :0,
				'dateSend' =>   $this->time ?$this->time->format('m/d/y') :0,
				'deleteKey' =>  $deletionKey ?: $id
			];

			// The variable sets
			if(count($variableSets)){
				$setsArray = [];
				foreach($variableSets as $variableSet){
					if(!is_array($variableSet)) continue;

					$arraySet = [];
					foreach($variableSet as $key => $value){
						$arraySet[] = "{$key},*,{$value}";
					}

					$setsArray[] = implode(',@,', $arraySet);
				}

				if(count($setsArray)){
					// Change the request function
					$this->function = 'msgSendWK';

					// Pass the sets to the request params
					$params['msgKey'] = implode('***', $setsArray);
				}
			}

			parent::__construct($client, $params);
		}

		/**
		 * Tells if the message was sent
		 * @return bool
		 */
		public function sent(){
			return $this->getResponse()->raw() == '1';
		}

		/**
		 * Returns the time remaining for the message is schedule to be sent later. Note that it returns a
		 * DateInterval object.
		 * @return bool|\DateInterval|null
		 */
		public function timeTillSending(){
			if(!$this->time) return null;

			return $this->time->diff(Carbon::now());
		}

		/**
		 * The message id
		 * @return mixed
		 */
		public function getMessageId(){
			return $this->params['msgId'];
		}

		/**
		 * The message deletion key
		 * @return mixed
		 */
		public function getDeletionKey(){
			return $this->params['deleteKey'];
		}

		/**
		 * Deletes the message, that if it's scheduled
		 * @return bool
		 * @throws \Exception
		 */
		public function cancel(){
			if(!$this->time || !$this->time->isFuture()) return false;

			// Request deletion
			$request = $this->client->createRequest('deleteSMS', [$this->getDeletionKey()]);
			$request->send();

			return $request->deleted();
		}
	}