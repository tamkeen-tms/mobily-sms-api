## About 
[Mobily.ws](https://www.mobily.ws) is the number one provider for SMS services in the gulf region, but many of their [APIs](https://www.mobily.ws/en/api-developers/php.rar.html) are outdated and badly designed. This is a non-official rewrite of their PHP API client, you will find it more flexible and up-to-date.

## Installation
You simply need to require it through Composer:

```
$ composer require tamkeenlms/mobily-sms-api
```

## How it works
You start by defining an API "Client", you can define multible API clients and chose with every request the client through which it will be made. Here's an example:

```php
  $client1 = new \MobilyAPI\Client( $username , $password , $paramDefaults, $setup );
```

#### A new *MobilyAPI\Client* accepts the following arguaments:
- **username**: Your account username
- **password**: The account password
- **paramDefault**: 
  - "**sender**": The default "sender" name for the SMSs.
  - "**domainName**": The application/website domain name through which you use the API. *This is automatically filled with the host name*.
  - "**applicationType**": This is fixed to "68" (I don't know what it's or what it does, but you are required to send it!)
  - "**lang**": This too is fixed, to "3", which - as it turned out - represents that the encoding used in the requests is UTF-8.
- **setup**  
  - "**base_uri**": The API base uri. You don't need to change this.
  - "**request_time_out**": The time out for the requests, in seconds
  - "**verify_ssl_certificate**": (Boolean) Whether to omit the SSL certificate verification or not. *it's false by default*

Here is an example:

```php
  $client1 = new \MobilyAPI\Client('username1', 'password2', ['sender' => 'Tamkeen LMS']);
  $client2 = new \MobilyAPI\Client('username2', 'password2', ['sender' => 'Tamkeen Company']);
```

----

## Requests
Through a `Client` you can make an API `request`. Of course the request you make will affect the account you chosen for the client through which you made the request. Here is an example for a request that checks the serivce/sending status:

```php
  // Create the request, under the $client1
  $request = $client1->createRequest('sendingStatus');
  
  // Send the request
  $response = $request->send(); // (Returns a "Response" instance)
```

Mobilys's API has a specific number of functions, each function is represented in this library as a `request`. Each request has its own class, that you can call directly. So, instead of calling a request through a `client` you can create a new request and pass it a  client. Here's how:

```php
  $serviceStatus = new \MobilyAPI\requests\SendingStatus( $client1 ); // Pass the client instance to the request class
  $response = $serviceStatus->send();
```

Each request has its own set of methods that are specific to it. Here is an example:

```php
  $request = $client1->createRequest('sendingStatus'); // Create
  $request->send(); // Send
  
  var_dump( $request->available() ); // The method "available" returns boolean whether the service is up or not 
```

Each request you make has these methods:
- **send()**: Which sends the request to the service API.
- **object()**: Returns the request object, which is an instance of *Guzzle HTTP Request*
- **getClient()**: Returns the `Client` which you made the request through
- **getParams()**: Returns an array of the params you passed along with the request
- **getResponse()**: Returns the response of the request, which is an instance of [`Response`](#responses)
- **successful()**: (Boolean) Tells you if the request to the service API was successful or not.
- **failed()**: (Boolean)

```php
  $request = $client1->createRequest('sendSMS', ['0123456789'], 'Hi there!');
  
  // The params
  $request->getParams(); // ['numbers' => ['0123456789'], 'message' => 'Hi there!']
  
  // The response
  $request->getResponse()->result(); // "message_sent"
  
  if($request->successful() && $request->sent()){
    // Request made successfully, and the message was sent successfully!
  }

  // The request client
  $request->getClient()->createRequest( ... )
```

BTW, you could pull this off in a one-liner, like so:
```php
	(new Client(...))->request('SendSMS', ['0123456789'], 'Beep boop ...');
	
	//Or
	$client2->createRequest('SendingStatus')->getResponse()->available();
```

----

## Responses
Each `request` you make returns an instance of the `Response` class, which offers the following:
- **object()**: Which is a [*Guzzle HTTP*](http://docs.guzzlephp.org/en/latest/psr7.html#responses) Response instance.
- **raw()**: Returns the response raw body of the request.
- **result()**: Returns the request result. What is that? well, each request returns a numerical code that represents the result of the request. To get the this numerical value you can use `raw()` but with `result()` you will get a code, like: "connection_failed", "message_sent", "not_enough_credit" .. etc. Each request has its own set or response codes, these code can be found inside the request class.

Example:
```php
  $serviceStatus = new \MobilyAPI\requests\SendingStatus( $client1 );
  $response = $serviceStatus->send(); // Returns the Response
  
  var_dump( $response->result() ); // "available"
  var_dump( $response->raw() ); // "1"
  
  // Guzzle Response object
  $response->object()->getStatusCode(); // "200" for example
  
  // A method available with the request
  $request->available(); // Boolean (Notice using $request not $response)
```

----
## Checking service status
```php
  $serviceStatus = new \MobilyAPI\requests\SendingStatus( $client1 );
  $serviceStatus->send();
  
  $serviceStatus->available(); // Available or not
```
----

## Sending a new SMS
Sending a new SMS via this client is done through the `Requests\SendSMS` request class:

```php
  $newSMS = new \MobilyAPI\requests\sendSMS( client, numbers, $message, $time, variableSets, messageId, sender, deletionKey );
  $newSMS->send();
  
  $newSMS->sent() // Boolea, marks whether the message was sent or not
```
And here are the args it takes:
- **client**: The `Client` instance, through which you want to send the SMS
- **numbers**: (Array) The receipient numbers. The original API docs dictates that each number should be in the global format, and with out 00 or + at the beginning.
- **message**: The message you want to send, UTF-8 encoded.
- **time**: [optional] Use in case you wanted to schedule this message to be sent later. Here you should provide a valid date/time/datetime format of the time/date you want the message scheduled in. You can also pass a Carbon instance (example below).
- **variableSets**: [optional] You can use this to send the message to multiple receipients with different values for each receipient. Meaning that the message will become like a template, and each receipient has his/her specific values/information that will be embeded in the associated message (example below).
- **messageId**: [optional] A unique id for the message. You really need to go back to the original docs of the official API and try to understand what this does. But in general it locks sending the same message to the same number within the same hour. The library will automatically pass a unique id if this argument is omitted.
- **sender**: [optional] That in case you wanted a different "sender" name for this message, else the default sender will be used. 
- **deletionKey**: [optional] Canceling/deleting a schedule message requires the "deleteKey" for this message, this is where you pass this key. By default the message id will be used here too.  

The `Requests\SendSMS` class has these methods:
- **sent()**: (Boolean) Tells if the message was sent and delieverd successfully.
- **timeTillSending()**: (DateInterval) If the message is scheduled to be sent later this method returns the time difference between now and the time in which it will be send.
- **getMessageId()**: Returns the message id, the one you picked, or of course the one the library generated for you. 
- **getDeletionKey()**: Returns the message deletion key. 
- **cancel()**: Use this method to cancel a schedule message

Here are the examples:
```php
  $client->createRequest('SendSMS', ['0123456789', '9876543210'], 'I AM YOUR FATHER')->send();
```

Schedule for later:
```php
  $client->createRequest('SendSMS', ['0123456789'], 'Bazinga', '2016-12-31 00:00:00');
  $client->createRequest('SendSMS', ['0123456789'], 'Stop googling your name!', Carbon::tomorrow()); // Send tomorrow the same time
  $client->createRequest('SendSMS', ['0123456789'], 'Smilly cat, Smilly cat', Carbon::today()->addDays(3)); // 12 AM 3 days from now
```
You might want to check [Carbon](http://carbon.nesbot.com/docs/#api-addsub), it's pure awesome.

You can cancel a scheduled new message like this:
```php
  $request = $client->createRequest('SendSMS', ['0123456789'], 'How you doin', '2016-12-31 18:30:00');
  
  $request->send(); // Schedule
  $request->cancel(); // Canel
```

Sending a template message:
```php
  $values = [
    ['name' => 'Michael', 'website' => 'Tamkeenlms.com'],
    ['name' => 'John', 'website' => 'Workflowy.com']
  ];
  
  $client->createRequest('SendSMS', ['0123456789', '9876543210'], 'Hey (name), check (website)', null, $values);
```
----

## Deleting a scheduled message
To delete a schedule message you need the message deletion key
```php
  $request = $client->createRequest('deleteSMS', ['messageXYZ']);
  $request->deleted(); // (Boolean) Marking whether the message was deleted or not
```

If you send a message through `Requests\SendSMS` you can get this message deletion key back using `getDeletionKey()`. Example:
```php
  $request = $client->createRequest('SendSMS', ['0123456789'], 'Yo!');
  $request->send();
  
  $messageDeletionKey = $request->getDeletionKey();
  
  // ...
  
  $request2 = $client->createRequest('deleteSMS', [$messageDeletionKey]);
```

----

### Retrieving your balance
```php
  $request = $client->createRequest('GetBalance');
  $request->send();
  
  $request->balance(); // ['remaining' => 50, 'total' => 100]
  $request->remaining(); // 50
  $request->total(); // 100
  $request->available(); // (Boolean) Has credit or not
```

Hope you find this useful, and please feel free to contact us any time with any questions.
Good luck

