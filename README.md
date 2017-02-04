PHP class for SurveyMonkey API.
==============================


Basic usage
----
```
$SM = new SurveyMonkey("myApiKey" , "myAccessToken");
$result = $SM->getSurveyList();
if ($result["success"]) print_r( $result["data"] );
else print_r($result["message"]);   // Print out the error message
```

All methods return an array containing a **success** boolean, and the **data** -or- an error **message**

Advanced
----
```
$SM = new SurveyMonkey("myApiKey" , "myAccessToken", 
    array(  // Override default API options (quite useless at the moment)
        'protocol' => 'http',                       // will not work.. they require SSL
        'hostname' => 'fake-api.surveymonkey.net'   // will also not work..
    ), 
    array(  // CURL override options
        CURLOPT_SSL_VERIFYPEER => false     // Better add cacert.pam, no?
        // ...<Any CURLOPT>...
    )
);
$result = $SM->getSurveyList(array(
    "fields" => array(
        "title",
        "page",
        "start_modified_at",
        "end_modified_at",
        "sort_by"
    ),
));
```

All methods
----

**getSurveyList**
```
/**
 * Returns a list of surveys owned or shared with the authenticated user
 * @see https://developer.surveymonkey.com/api/v3/#surveys
 * @param array $params optional request array
 * @return array Result
 */
public function getSurveyList($params = array()){}
```

**getSurveyDetails**
```
/**
 * Retrieve a given survey's metadata.
 * @see https://developer.surveymonkey.com/api/v3/#surveys-id-collectors
 * @param string $surveyId Survey ID
 * @param array $params optional request array
 * @return array Results
 */
public function getSurveyDetails($surveyId, $params = array()){}
```

**getCollectorList**
```
/**
 * Retrieves a paged list of collectors for a survey in a user's account.
 * @see https://developer.surveymonkey.com/api/v3/#collectors-id
 * @param string $surveyId Survey ID
 * @param array $params optional request array
 * @return array Results
 */
public function getCollectorList($surveyId, $params = array()){}
```

**getRespondentList**
```
/**
 * Retrieves a paged list of respondents for a given survey and optionally collector
 * @see https://developer.surveymonkey.com/api/v3/#surveys-id-responses-bulk
 * @param string $surveyId Survey ID
 * @param array $params optional request array
 * @return array Results
 */
public function getRespondentList($surveyId, $params = array()){}
```

**getResponses**
```
/**
 * Takes a list of respondent and returns the responses that correlate to them.
 * @param string $surveyId Survey ID
 * @param array $params optional request array
 * @return array Results
 */
public function getResponses($surveyId, $params = []){}
```

**createCollector**
```
/**
* Retrieves a paged list of templates provided by survey monkey.
* @see https://developer.surveymonkey.com/api/v3/#surveys-id-collectors
* @param string $surveyId Survey ID
* @param string $collectorName optional Collector Name - defaults to 'New Link'
* @param string $collectorType required Collector Type - only 'weblink' currently supported
* @param array $params optional request array
* @return array Results
*/
public function createCollector($surveyId, $collectorName = null, $collectorType = 'weblink'){}
```

**createFlow**
```
/**
 * Create a survey, email collector and email message based on a template or existing survey.
 * @see https://developer.surveymonkey.com/api/v3/#surveys
 * @param string $surveyTitle Survey Title
 * @param string $from_survey_id Existing survey
 * @param array $params optional request array
 * @return array Results
 */
public function createFlow($surveyTitle, $from_survey_id, $params = array()){}
```

**updateSurvey**
```
/**
 * Modifies a survey’s title, nickname or language.
 * @see https://developer.surveymonkey.net/api/v3/#surveys
 * @param string $surveyID Survey ID
 * @param array $params optional request array
 * @return array Results
 */
public function updateSurvey($surveyID, $params = array()){}
```

**createMessage**
```
/**
 * Creates a message
 * @see https://developer.surveymonkey.com/api/v3/#collectors-id-messages
 * @param string $collectorIdID Colector ID
 * @param array $params optional request array
 * @return array Results
 */
```

**createBulkRecipients**
```
/**
 * Creates multiple recipients for a message
 * @see https://developer.surveymonkey.com/api/v3/#collectors-id-messages-id-recipients-bulk3/#collectors-id-messages
 * @param string $collectorId Colector ID
 * @param string $messageId Message ID
 * @param array $params optional request array
 * @return array Results
 */
```

**sendMessage**
```
/**
 * Send or schedule to send an existing message to all message recipients.
 * @see https://developer.surveymonkey.com/api/v3/#collectors-id-messages-id-send
 * @param string $collectorId Colector ID
 * @param string $messageId Message ID
 * @param array $params optional request array
 * @return array Results
 */
```

API version
-----------
v3


Tests
-----
*TODO*


License
----
**No** rights reserved.  
*Do whatever you want with it,  It's free*
