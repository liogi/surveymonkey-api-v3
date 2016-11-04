<?php

/**
 * Class for SurveyMonkey API v3
 * @package default
 */
class SurveyMonkey
{
    /**
     * @const HTTP response code: Success
     */
    const HTTP_RESPONSE_CODE_SUCCESS = 200;

    /**
     * @const HTTP response code: Success and creation
     */
    const HTTP_RESPONSE_CODE_SUCCESS_CREATION = 201;

    /**
     * @var string API key
     * @access protected
     */
    protected $_apiKey;

    /**
     * @var string API access token
     * @access protected
     */
    protected $_accessToken;

    /**
     * @var string API protocol
     * @access protected
     */
    protected $_protocol;

    /**
     * @var string API hostname
     * @access protected
     */
    protected $_hostname;

    /**
     * @var string API resource path
     * @access protected
     */
    protected $_resource;

    /**
     * @var string API version
     * @access protected
     */
    protected $_version;

    /**
     * @var array (optional) cURL connection options
     * @access protected
     */
    protected $_connectionOptions;

    /**
     * @var resource $conn The client connection instance to use.
     * @access private
     */
    private $conn = null;

    /**
     * The SurveyMonkey Constructor.
     *
     * This method is used to create a new SurveyMonkey object with a connection to a
     * specific api key and access token
     *
     * @param string $apiKey A valid api key
     * @param string $accessToken A valid access token
     * @param array $options (optional) An array of options
     * @param array $connectionOptions (optional) cURL connection options
     * @throws SurveyMonkey_Exception If an error occurs creating the instance.
     * @return SurveyMonkey A unique SurveyMonkey instance.
     */
    public function __construct($apiKey, $accessToken, $options = array(), $connectionOptions = array())
    {
        if (empty($apiKey)) {
            throw new SurveyMonkey_Exception('Missing apiKey');
        }

        if (empty($accessToken)) {
            throw new SurveyMonkey_Exception('Missing accessToken');
        }

        $this->_apiKey = $apiKey;
        $this->_accessToken = $accessToken;

        $this->_protocol = (!empty($options['protocol'])) ? $options['protocol'] : 'https';
        $this->_hostname = (!empty($options['hostname'])) ? $options['hostname'] : 'api.surveymonkey.net';
        $this->_version = (!empty($options['version'])) ? $options['version'] : 'v3';

        $this->_connectionOptions = $connectionOptions;
    }

    /**
     * Return an error
     * @param string $msg Error message
     * @return array Result
     */
    protected function failure($msg)
    {
        return array(
            'success' => false,
            'message' => $msg
        );
    }

    /**
     * Return a success with data
     * @param string $data Payload
     * @return array Result
     */
    protected function success($data)
    {
        return array(
            'success' => true,
            'data' => $data
        );
    }

    /**
     * Get the connection
     * @return boolean
     */
    protected function getConnection()
    {
        $this->conn = curl_init();
        return is_resource($this->conn);
    }

    /**
     * Build the request URI
     * @param string $method API method to call
     * @return string Constructed URI
     */
    protected function buildUri($method)
    {
        return $this->_protocol . '://' . $this->_hostname . '/' . $this->_version . '/' . $method . '?api_key=' . $this->_apiKey;
    }

    /**
     * build uri with optional parameters for GET request
     * @param string $uri API url
     * @param array $params Parameters array
     * @return string $uri
     */
    protected function parametersGETRequest($uri, $params)
    {
        foreach ($params as $key => $param) {
            $uri .= '&' . $key . '=' . $param;
        }

        return $uri;
    }

    /**
     * Close the connection
     */
    protected function closeConnection()
    {
        curl_close($this->conn);
    }

    /**
     * Run the
     * @param string $method API method to run
     * @param array $params Parameters array
     * @return array Results
     */
    protected function run($method, $params = array(), $type)
    {
        if (!is_resource($this->conn) && !$this->getConnection()) {
            return $this->failure('Can not initialize connection');
        }

        $request_url = $this->buildUri($method);

        if ($type == 'GET') {
            $request_url = $this->parametersGETRequest($request_url, $params);
        } else {

            $type == 'POST' ? curl_setopt($this->conn, CURLOPT_POST, true) : curl_setopt($this->conn, CURLOPT_CUSTOMREQUEST, $type);

            if (!empty($params)) {
                curl_setopt($this->conn, CURLOPT_POSTFIELDS, json_encode($params));
            }
        }

        curl_setopt($this->conn, CURLOPT_URL, $request_url);  // URL to request to
        curl_setopt($this->conn, CURLOPT_RETURNTRANSFER, 1);   // return into a variable
        $headers = array('Content-type: application/json', 'Authorization: Bearer ' . $this->_accessToken);
        curl_setopt($this->conn, CURLOPT_HTTPHEADER, $headers); // custom headers
        curl_setopt($this->conn, CURLOPT_HEADER, false);     // return into a variable
        curl_setopt_array($this->conn, $this->_connectionOptions);  // (optional) additional options

        $result = curl_exec($this->conn);

        if ($result === false) {
            return $this->failure('Curl Error: ' . curl_error($this->conn));
        }

        $responseCode = curl_getinfo($this->conn, CURLINFO_HTTP_CODE);

        if ($responseCode != self::HTTP_RESPONSE_CODE_SUCCESS && $responseCode != self::HTTP_RESPONSE_CODE_SUCCESS_CREATION) {
            return $this->failure('Error [' . $responseCode . ']: ' . $result);
        }

        $this->closeConnection();

        $parsedResult = json_decode($result, true);
        $jsonErr = json_last_error();

        if ($parsedResult === null && $jsonErr !== JSON_ERROR_NONE) {
            return $this->failure("Error [$jsonErr] parsing result JSON");
        }

        return $this->success($parsedResult);
    }

    /***************************
     * SurveyMonkey API methods
     ***************************/

    /**
     * Create a survey, email collector and email message based on a template or existing survey.
     * @see https://developer.surveymonkey.com/api/v3/#surveys
     * @param string $surveyTitle Survey Title
     * @param string $from_survey_id Existing survey
     * @param array $params optional request array
     * @return array Results
     */
    public function createFlow($params = array())
    {
        if (!isset($params['nickname']) && isset($params['title'])) {
            $params['nickname'] = $params['title'];
        }

        return $this->run('surveys', $params, 'POST');
    }

    /**
     * Modifies a survey’s title, nickname or language.
     * @see https://developer.surveymonkey.net/api/v3/#surveys
     * @param string $surveyID Survey ID
     * @param array $params optional request array
     * @return array Results
     */
    public function updateSurvey($surveyID, $params = array())
    {
        return $this->run('surveys/' . $surveyID, $params, 'PATCH');
    }

    /**
     * Returns a list of surveys owned or shared with the authenticated user
     * @see https://developer.surveymonkey.com/api/v3/#surveys
     * @param array $params optional request array
     * @return array Result
     */
    public function getSurveyList($params = array())
    {
        return $this->run('surveys', $params, "GET");
    }

    /**
     * Retrieve a given survey's metadata.
     * @see https://developer.surveymonkey.com/api/v3/#surveys-id-collectors
     * @param string $surveyId Survey ID
     * @param array $params optional request array
     * @return array Results
     */
    public function getSurveyDetails($surveyId, $params = array())
    {

        return $this->run('surveys/' . $surveyId . '/details', $params, 'GET');
    }

    /**
     * Takes a list of respondent and returns the responses that correlate to them.
     * @param string $surveyId Survey ID
     * @param array $params optional request array
     * @return array Results
     */
    public function getResponses($surveyId, $params = [])
    {
        $respondents = $this->getRespondentList($surveyId, $params);

        return $respondents['data']['data'];
    }

    /**
     * Retrieves a paged list of respondents for a given survey and optionally collector
     * @see https://developer.surveymonkey.com/api/v3/#surveys-id-responses-bulk
     * @param string $surveyId Survey ID
     * @param array $params optional request array
     * @return array Results
     */
    public function getRespondentList($surveyId, $params = array())
    {

        return $this->run('surveys/' . $surveyId . '/responses/bulk', $params, 'GET');
    }

    /**
     * Retrieves a paged list of templates provided by survey monkey.
     * @see https://developer.surveymonkey.com/api/v3/#surveys-id-collectors
     * @param string $surveyId Survey ID
     * @param string $collectorName optional Collector Name - defaults to 'New Link'
     * @param string $collectorType required Collector Type - only 'weblink' currently supported
     * @param array $params optional request array
     * @return array Results
     */
    public function createCollector($surveyId, $collectorName = null, $collectorType = 'weblink')
    {
        $params = [
            'type' => $collectorType,
            'name' => $collectorName
        ];

        return $this->run('surveys/' . $surveyId . '/collectors', $params, 'POST');
    }

    /**
     * Retrieves a paged list of collectors for a survey in a user's account.
     * @see https://developer.surveymonkey.com/api/v3/#collectors-id
     * @param string $surveyId Survey ID
     * @param array $params optional request array
     * @return array Results
     */
    public function getCollectorList($surveyId, $params = array())
    {
        return $this->run('surveys/' . $surveyId . '/collectors', $params, 'GET');
    }
}

/**
 * A basic class for SurveyMonkey Exceptions.
 * @package default
 */
class SurveyMonkey_Exception extends Exception
{
}
