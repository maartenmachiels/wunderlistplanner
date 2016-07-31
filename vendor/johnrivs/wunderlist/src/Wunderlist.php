<?php namespace JohnRivs\Wunderlist;

use Exception;
use GuzzleHttp\Client as HttpClient;

class Wunderlist {

    use Authorization,
        Avatar,
        Folder,
        Lists,
        Note,
        Reminder,
        Subtask,
        Task,
        Comment,
        User,
        Webhook;

    /**
     * Base URL for the Wunderlist API.
     * 
     * @var string
     */
    protected $baseUrl = 'http://a.wunderlist.com/api/v1/';

    /**
     * HTTP status code returned by each request.
     * 
     * @var int
     */
    protected $statusCode;

    /**
     * HTTP Client.
     * 
     * @var \Guzzlehttp\Client
     */
    protected $http;

    /**
     * The Wunderlist app client id.
     * 
     * @var string
     */
    public $clientId;

    /**
     * The Wunderlist app client secret.
     * 
     * @var string
     */
    public $clientSecret;

    /**
     * The Wunderlist app access token.
     * 
     * @var string
     */
    protected $accessToken;

    public function __construct($clientId, $clientSecret, $accessToken)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->accessToken  = $accessToken;

        $this->http         = $this->getHttpClient();
    }

    /**
     * Master call. It makes the requests to the Wunderlist API endpoints.
     * 
     * @param  string $httpMethod
     * @param  string $endpoint
     * @param  array  $parameters
     * @return array
     */
    public function call($httpMethod, $endpoint, array $parameters = [])
    {
        // Every request to the Wunderlist API
        // must provide the app client id
        // and access token.
        $parameters = array_merge($parameters, [
            'headers' => $this->getHeaders(),
        ]);

        // We build the request
        // passing any parameter (if any).
        $request = $this->http->createRequest($httpMethod, $this->baseUrl . $endpoint, $parameters);

        // We send the request.
        $response = $this->http->send($request);

        // We store the returned HTTP status code
        // in case it's needed.
        $this->statusCode = $response->getStatusCode();

        // Finally, we return the contents of the response.
        return $response->json();
    }

    /**
     * Get the HTTP status code from the last response.
     * 
     * @return int
     */
    public function getStatusCode()
    {
        if (empty($this->statusCode)) throw new Exception('An HTTP status code has not been set. Make sure you ask for this AFTER you make a request to the API.');

        return (int) $this->statusCode;
    }

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     * 
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        return new HttpClient;
    }

    /**
     * The headers needed for (almost) every request
     * to the Wunderlist API.
     * 
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'X-Client-ID'    => $this->clientId,
            'X-Access-Token' => $this->accessToken,
            'Content-Type'   => 'application/json'
        ];
    }

    /**
     * Checks if the provided attributes contain certain fields.
     * 
     * @param  array  $requirements A list of required attributes.
     * @param  array  $attributes   The provided attributes
     * @return \Exception
     */
    protected function requires(array $requirements, array $attributes)
    {
        foreach ($requirements as $required) {
            if ( ! array_key_exists($required, $attributes)) {
                throw new Exception("The '{$required}' attribute is required.");
            }
        }
    }
    
}
