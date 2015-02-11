<?php

namespace
{

/**
 * PHP Calendar tool
 *
 * A standalone PHP program that can read, update and create events in Google Calendar
 *
 * Requirements:
 *   1. A valid Google Server Account
 *   2. PHP OpenSSL extension - for the certificate
 *   3. PHP Curl extension - for API requests
 *   4. PHP version >= 5.3
 *
 * @package PHPCalendarTool
 * @author Alexandre S. <alexandre67fr@gmail.com>
 * @version 1.0
 */

/**
 * Settings of the application
 *
 * They can be obtained here in {@link https://code.google.com/apis/console Google Console}
 * Choose "Credentials" → "OAuth" → "Create new Client ID" → "Service account"
 * @link https://code.google.com/apis/console Google Console
 *
 *
 * After credentials are created, please change the three settings below.
 * Also, please do not forget to enable Google Calendar API in "APIs & Auth" → APIs
 */

/**
 * Google API: Client ID
 *
 * @global $client_id string
 */
$client_id = '1079666599882-jj99t30kk5tgpns5lqqcqioap4k22oho.apps.googleusercontent.com';

/**
 * Google API: Service account name
 *
 * @global $service_account_name string
 */
$service_account_name = '1079666599882-jj99t30kk5tgpns5lqqcqioap4k22oho@developer.gserviceaccount.com';

/**
 * Google API: Path to certificate file
 *
 * It should be absolute, or relative to the executed file
 * E.g.: __DIR__ . "/certificate.p12"
 *
 * @global $key_file_location string
 * Path to the certificate file
 */
global $key_file_location;
$key_file_location =  __DIR__ . '/../keys/certificate.p12';


/**
 * Defines if we should throw exceptions or not
 *
 * @global $throw_exceptions
 */
global $throw_exceptions;
$throw_exceptions = FALSE;

/**
 * Autoload classes
 *
 * @param string $class
 * Class name. Includes namespace
 *
 * @internal
 */
function phpcalendar_autoloader($class)
{
  // Remove namespace prefix
  $class = str_replace('PHPCalendar\\', '', $class);

  $file = __DIR__ . '/classes/' . $class . '.php';
  @include $file;
}
spl_autoload_register('phpcalendar_autoloader');

/**
 * Gets the list of all events in all calendars, except deleted and events without both start and end datetime.
 *
 * Examples:
 *
 * <code>
 * //
 * // Get all events in all calendars
 * //
 * GetEventList();
 *
 * //
 * // Get all events from January, 1st, 2015 at 00:00 until February, 1st, 2015 23:59:59
 * //
 * GetEventList('2015-01-01', '2015-02-01');
 *
 * //
 * // Get all events starting from January, 15th, 2015
 * //
 * GetEventList('2015-01-15');
 *
 * //
 * // Get all events starting until January, 15th, 2015 at 12:00
 * //
 * GetEventList(NULL, '2015-01-15 12:00');
 * GetEventList(NULL, 1421319600); // UNIX timestamp is also supported
 * GetEventList(FALSE, '2015-01-15 12:00');
 * GetEventList('', '2015-01-15 12:00');
 * GetEventList(0, '2015-01-15 12:00 GMT+1'); // If timezone is set to Central Europe
 *
 * //
 * // Get all events from the first calendar
 * //
 * $calendars = PHPCalendar\Calendar::all();
 * $first_calendar = array_shift( $calendars );
 * $events = GetEventList(NULL, NULL, $first_calendar);
 * $events = GetEventList(NULL, NULL, $first_calendar->id); // this is the same
 * </code>
 *
 * Example of what is returned:
 *
 * <code>
 *   Array
 *   (
 *       [0] => Array
 *           (
 *               [Calendar] => ilrvagn6kkgugheatauheptqts@group.calendar.google.com
 *               [CalendarEventID] => ajo7984bbeqpgarv8fnpk00vuc
 *               [Heading] => Full-day event
 *               [Location] => 
 *               [Description] => 
 *               [StartDatetime] => 2015-02-11 00:00:00
 *               [EndDatetime] => 2015-02-12 00:00:00
 *           )
 *
 *       [1] => Array
 *           (
 *               [Calendar] => ilrvagn6kkgugheatauheptqts@group.calendar.google.com
 *               [CalendarEventID] => mi9huneutoq944olc0899qd2ng
 *               [Heading] => From 11 to 12 event
 *               [Location] => 
 *               [Description] => 
 *               [StartDatetime] => 2015-02-11 11:00:00
 *               [EndDatetime] => 2015-02-11 12:00:00
 *           )
 *
 *   )
 * </code>
 *
 * @uses PHPCalendar\Event
 * @uses PHPCalendar\Calendar
 * @link https://developers.google.com/google-apps/calendar/v3/reference/events/list
 *
 * @param datetime|timestamp $start_datetime 
 * Date time or timestamp of the oldest returned event. Optional.
 * @param datetime|timestamp $end_datetime     
 * Date time or timestamp of the newest returned event. Optional.
 * @param mixed $calendar         
 * Show events only from this calendar. Can be an object or calendar ID. But also an array of object or an array of calendar IDs Optional.
 *
 * @return PHPCalendar\Event[] Array of events. Or an empty array if no events were found.
 */
function GetEventList($start_datetime=NULL, $end_datetime=NULL, $calendars=NULL)
{
  try 
  {
    $events = array();

    // If the end date is just a date (no time),
    // then set end date to 1 second before mightnight.
    // Or else, events from that particular date will not be displayed
    if ( preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_datetime) )
      $end_datetime .= ' 23:59:59';

    $start_datetime = PHPCalendar\DateTime::timestamp( $start_datetime );
    $end_datetime   = PHPCalendar\DateTime::timestamp( $end_datetime );

    // Start and End date for Google are exclusive
    // So we need to correct them
    // @link https://developers.google.com/google-apps/calendar/v3/reference/events/list#timeMax
    if ( $start_datetime )
      $start_datetime -= 1;
    if ( $end_datetime )
      $end_datetime += 1;

    if ( ! $calendars )
      $calendars = PHPCalendar\Calendar::all();
    else
    {
      // Something was supplied
      // Convert it to an array if it is not already
      if ( ! is_array( $calendars ) )
        $calendars = array( $calendars );
      
      // Convert calendar IDs to calendar Objects is they are not
      foreach ( $calendars as $key => $calendar )
        $calendars[ $key ] = PHPCalendar\Calendar::find( $calendar );

    }

    // Now we have the list of calendars
    // Let's get the list of events of each calendar
    foreach ( $calendars as $calendar )
    {

      $fetched_events = $calendar->events(array(
        // Since we have only timestamps, we need to format them
        // @link https://developers.google.com/google-apps/calendar/concepts#timed_events
        'timeMin' => ($start_datetime ? PHPCalendar\DateTime::date3339( $start_datetime ) : NULL),
        'timeMax' => ($end_datetime ? PHPCalendar\DateTime::date3339( $end_datetime ) : NULL),
      ));
      $events = array_merge( $events, $fetched_events );

    }

    // timeMax parameter is not the same as $end_datetime
    // We need to remove events that start after $end_datetime or before $start_datetime
    foreach ( $events as $key => $event )
    {
      if ( $start_datetime )
        if ( $event->start <= $start_datetime )
          unset( $events[ $key ] );

      if ( $end_datetime )
        if ( $event->start >= $end_datetime )
          unset( $events[ $key ] );
    }
    
    $events = array_values( $events );

    // Convert Event objects into array
    $events_array = array();
    foreach ($events as $event)
      $events_array[] = array(
        "Calendar"         => $event->calendar->id,
        "CalendarEventID"  => $event->id,
        "Heading"          => $event->summary,
        "Location"         => $event->location,
        "Description"      => $event->description,
        "StartDatetime"    => date("Y-m-d H:i:s", $event->start),
        "EndDatetime"      => date("Y-m-d H:i:s", $event->end),
      );
  
    return $events_array;

  }
  catch (Exception $e)
  {
    global $throw_exceptions;
    if ( $throw_exceptions )
      throw $e;
    return array();
  }
}

/**
 * Updates a calendar event.
 *
 * If some arguments are supplied as NULL, than the corresponding property will not be changed.
 *
 * Examples: the same as {@link CreateCalendarEvent()}, but with $event parameter.
 *
 * @uses PHPCalendar\Event
 * @uses PHPCalendar\Calendar
 * @link https://developers.google.com/google-apps/calendar/v3/reference/events/patch
 *
 * @param PHPCalendar\Calendar|string $calendar         
 * Calendar to which an event belongs to. Can be an object or calendar ID.
 * @param PHPCalendar\Event|string $event            
 * Event which is being updated. Object or event ID.
 * @param string $heading          
 * Title of the event. Optional.
 * @param string $location         
 * Location of the event. Optional.
 * @param string $description      
 * Description of the event. Optional.
 * @param datetime|timestamp $start_datetime   
 * Date time or timestamp when the event starts. Optional. Submit in format 'YYYY-mm-ddd' for all-day event.
 * @param datetime|timestamp $end_datetime     
 * Date time or timestamp when the event ends. Optional. Submit in format 'YYYY-mm-ddd' for all-day event.
 *
 * @return boolean TRUE on success, FALSE on failure.
 *
 */
function SetCalendarEvent(
  $calendar, 
  $event, 
  $heading=NULL, 
  $location=NULL, 
  $description=NULL, 
  $start_datetime=NULL, 
  $end_datetime=NULL
) {

  try 
  {

    $calendar = PHPCalendar\Calendar::find( $calendar );
    $event    = $calendar->event( $event );

    $event->summary = $heading;
    $event->location = $location;
    $event->description = $description;
    $event->start = $start_datetime;
    $event->end   = $end_datetime;

    $event->save();

    return TRUE;

  }
  catch (Exception $e)
  {
    global $throw_exceptions;
    if ( $throw_exceptions )
      throw $e;
    return FALSE;
  }
}

/**
 * Creates a calendar event.
 *
 * Example:
 *
 * <code>
 * // Create 'My Test Event' on February, 11th, 2015, from 11:00 to 12:00
 * CreateCalendarEvent('alexandre67fr@gmail.com', 'My Test Event', NULL, NULL, '2015-02-11 11:00', '2015-02-11 12:00');
 * // Create 'My Second Test Event' on February, 11th, 2015, for the whole day
 * CreateCalendarEvent('alexandre67fr@gmail.com', 'My Second Test Event', NULL, NULL, '2015-02-11');
 * </code>
 *
 * @uses PHPCalendar\Event
 * @uses PHPCalendar\Calendar
 * @link https://developers.google.com/google-apps/calendar/v3/reference/events/insert
 *
 * @param PHPCalendar\Calendar|string $calendar         
 * Calendar where the event is being created. Can be an object or calendar ID.
 * @param string $heading          
 * Title of the event. Optional.
 * @param string $location         
 * Location of the event. Optional.
 * @param string $description      
 * Description of the event. Optional.
 * @param datetime|timestamp $start_datetime   
 * Date time or timestamp when the event starts. Optional.
 * @param datetime|timestamp $end_datetime     
 * Date time or timestamp when the event ends. Optional.
 *
 * @return boolean TRUE on success, FALSE on failure.
 *
 */
function CreateCalendarEvent(
  $calendar, 
  $heading=NULL, 
  $location=NULL, 
  $description=NULL, 
  $start_datetime=NULL, 
  $end_datetime=NULL
) {

  try
  {

    $calendar = PHPCalendar\Calendar::find( $calendar );
    $event    = new PHPCalendar\Event( NULL, $calendar );

    $event->summary = $heading;
    $event->location = $location;
    $event->description = $description;
    if ( $start_datetime )
      $event->start = $start_datetime;
    if ( $end_datetime )
      $event->end = $end_datetime;

    $event->save();

    return TRUE;

  }
  catch (Exception $e)
  {
    global $throw_exceptions;
    if ( $throw_exceptions )
      throw $e;
    return FALSE;
  }
}

}


namespace PHPCalendar
{




/**
 * Calendar Event Class 
 * @extends Resource
 * @package PHPCalendarTool
 */
class Calendar extends Resource
{

  /**
   * Get events of this calendar
   *
   * @param array $filter
   * Filter options of the event
   *
   * @return PHPCalendar\Event[]
   */
  public function events( $filter = array() )
  {
    $list = new EventList( $this );
    $events = $list->getList( $filter );
    foreach ($events as &$event)
      $event->calendar = $this;
    return $events;
  }

  public function deleteAllEvents()
  {
    $events = $this->events();
    foreach ( $events as $event )
      $event->delete();
  }

  /**
   * Get event of this calendar
   *
   * @param $id string|PHPCalendar\Event 
   * Event ID or event object
   *
   * @return PHPCalendar\Event
   */
  public function event( $id )
  {
    return Event::find( $id, $this );
  }

  public function endpoint()
  {
    return "calendars/" . $this->id;
  }
}





/**
 * Calendar Event Class 
 * @extends ResourceList
 * @package PHPCalendarTool
 */
class CalendarList extends ResourceList
{

  public function endpoint()
  {
    return "users/me/calendarList";
  }

}





abstract class DateTime
{

  /**
   * Format string into a timestamp
   *
   * @param string $string 
   * Input string
   *
   * @return int Timestamp in Unix format
   */
  public static function timestamp( $string )
  {
    date_default_timezone_set('Europe/Paris');
    if ( ! $string )
      return NULL;
    if ( is_numeric( $string ) )
      return intval( $string );
    $ts = strtotime( $string );
    if ( $ts === FALSE )
      throw new Exception("Failed to convert string '$string' into a timestamp.");
    return $ts;
  }

  /**
   * Get date in RFC3339
   * For example used in XML/Atom
   *
   * @param integer $timestamp
   * @return string date in RFC3339
   * @author Boris Korobkov
   */
  public static function date3339( $timestamp = 0 ) {

    if ( $timestamp === NULL )
      return NULL;

    if ( ! $timestamp )
      $timestamp = time();
    
    $date = date('Y-m-d\TH:i:s', $timestamp);

    $matches = array();
    if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) 
      $date .= $matches[1].$matches[2].':'.$matches[3];
    else
      $date .= 'Z';
    
    return $date;
  }

}




/**
 * Calendar Event Class 
 * @extends Resource
 * @package PHPCalendarTool
 */
class Event extends Resource
{
  /**
   * Calendar that this event belong to
   * @param string|int $id 
   * @param PHPCalendar\Calendar $calendar
   */
  public $calendar;

  public $required_parameters = array(
    'PUT'  => array('end', 'start'),
    'POST' => array('end', 'start'),
  );

  public function __construct( $id = NULL, $calendar = NULL )
  {
    $this->calendar = $calendar;
    parent::__construct( $id );
  }

  public function __get($key)
  {
    $value = parent::__get( $key );
    if ( in_array( $key, array('start', 'end') ) )
    {
      $formatted = ( isset( $this->data[$key]->dateTime ) ? $this->data[$key]->dateTime : $this->data[$key]->date );
      $value = strtotime( $formatted );
    }
    return $value;
  }

  public function __set($key, $value)
  {
    if ( in_array( $key, array('start', 'end') ) )
    {
      if ( !isset( $this->data[ $key ] ) )
        $this->data[ $key ] = new \stdClass();

      $other_key = ( $key == 'start' ? 'end' : 'start' );

      $ts = DateTime::timestamp( $value );
      if ( $ts )
      {
        if ( preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) )
        {
          $value = date('Y-m-d', $ts);
          $this->data[$key]->date = $value;
          unset( $this->data[$key]->dateTime );
          // We cannot submit "date" and "dateTime" at the same time
          if ( isset( $this->data[$other_key]->dateTime ) )
          {
            $this->data[$other_key]->date = date('Y-m-d', DateTime::timestamp( $this->data[$other_key]->dateTime ));
            unset( $this->data[$other_key]->dateTime );
          }
        }
        else
        {
          $formatted = DateTime::date3339( $ts );
          $this->data[$key]->dateTime = $formatted;
          unset( $this->data[$key]->date );
          // We cannot submit "date" and "dateTime" at the same time
          if ( isset( $this->data[$other_key]->date ) )
          {
            $this->data[$other_key]->dateTime = DateTime::date3339( DateTime::timestamp( $this->data[$other_key]->date ));
            unset( $this->data[$other_key]->date );
          }
        }
      }
      return;
    }
    return parent::__set($key, $value);
  }

  public function endpoint()
  {
    $url = $this->calendar->endpoint() . "/events";
    if ( ! $this->id )
      return $url;
    return $url . "/" . $this->id;
  }

  public function save()
  {
    // Set start and end time if they were not set
    // They are required by Google API
    
    //print_r($this->data);

    if ( ! isset( $this->data[ 'start' ] ) )
      $this->data[ 'start' ] = new \stdClass();

    if (
      ! isset( $this->data['start']->date )
      and
      ! isset( $this->data['start']->dateTime )
    )
      $this->data['start']->date = date('Y-m-d');

    if ( ! isset( $this->data['end'] ) or ( $this->start > $this->end ) )
    {
      $this->data['end'] = clone $this->data['start'];
      if ( isset( $this->data['end']->date ) )
        $this->data['end']->date = date('Y-m-d', strtotime( $this->data['start']->date ) + 24 * 60 * 60 + 1);
    }

    if (
      ! isset( $this->data['end']->date )
      and
      ! isset( $this->data['end']->dateTime )
    )
      $this->data['end'] = clone $this->data['start'];

    return parent::save();
  }

  public static function find( $id, $calendar=NULL )
  {
    $class = get_called_class();
    if ( $id instanceof $class )
      return $id;
    return new $class( $id, $calendar );
  }

}





/**
 * Calendar Event List Class 
 * @extends ResourceList
 * @package PHPCalendarTool
 */
class EventList extends ResourceList
{

  /**
   * Calendar that these events belong to
   * @param PHPCalendar\Calendar $calendar
   */
  public $calendar;

  public function __construct( $calendar )
  {
    $this->calendar = $calendar;
  }

  public function endpoint()
  {
    return $this->calendar->endpoint() . "/events";
  }

}





/**
 * Calendar Exception Class 
 * @package PHPCalendarTool
 * @extends \Exception
 */
class Exception extends \Exception
{
}





/**
 * Abstract Auth class
 *
 * @link https://github.com/wanze/Google-Analytics-API-PHP/blob/master/GoogleAnalyticsAPI.class.php
 * @copyright {@link https://github.com/wanze/Google-Analytics-API-PHP}
 *
 */
abstract class GoogleOauth {

  const TOKEN_URL = 'https://accounts.google.com/o/oauth2/token';
  const SCOPE_URL = 'https://www.googleapis.com/auth/calendar';

  protected $assoc = true;
  protected $clientId = '';

  public function __set($key, $value)
  {
    $this->{$key} = $value;
  }

  public function setClientId($id)
  {
    $this->clientId = $id;
  }

  public function returnObjects($bool)
  {
     $this->assoc = !$bool;
  }

  /**
   * To be implemented by the subclasses
   *
   */
  public function getAccessToken($data=null) {}
}




/**
 * Oauth 2.0 for service applications requiring a private key
 * openssl extension for PHP is required!
 * @extends GoogleOauth
 *
 * @link https://github.com/wanze/Google-Analytics-API-PHP/blob/master/GoogleAnalyticsAPI.class.php
 * @copyright {@link https://github.com/wanze/Google-Analytics-API-PHP}
 *
 */
class GoogleOauthService extends GoogleOauth {
  const MAX_LIFETIME_SECONDS = 3600;
  const GRANT_TYPE = 'urn:ietf:params:oauth:grant-type:jwt-bearer';

  protected $email = '';
  protected $privateKey = null;
  protected $password = 'notasecret';
  /**
   * Constructor
   *
   * @access public
   * @param string $clientId (default: '') Client-ID of your project from the Google APIs console
   * @param string $email (default: '') E-Mail address of your project from the Google APIs console
   * @param mixed $privateKey (default: null) Path to your private key file (*.p12)
   */
  public function __construct($clientId='', $email='', $privateKey=null) {
    if (!function_exists('openssl_sign')) throw new Exception('openssl extension for PHP is needed.');
    $this->clientId = $clientId;
    $this->email = $email;
    $this->privateKey = $privateKey;
  }
  public function setEmail($email) {
    $this->email = $email;
  }
  public function setPrivateKey($key) {
    $this->privateKey = $key;
  }
  /**
   * Get the accessToken in exchange with the JWT
   *
   * @access public
   * @param mixed $data (default: null) No data needed in this implementation
   * @return array Array with keys: access_token, expires_in
   */
  public function getAccessToken($data=null) {
    if (!$this->clientId || !$this->email || !$this->privateKey) {
      throw new Exception('You must provide the clientId, email and a path to your private Key');
    }
    $jwt = $this->generateSignedJWT();
    $params = array(
      'grant_type' => self::GRANT_TYPE,
      'assertion' => $jwt,
    );
    $auth = Http::curl(GoogleOauth::TOKEN_URL, $params, 'POST');
    return json_decode($auth, $this->assoc);
  }
  /**
   * Generate and sign a JWT request
   * See: https://developers.google.com/accounts/docs/OAuth2ServiceAccount
   *
   * @access protected
   */
  protected function generateSignedJWT() {
    // Check if a valid privateKey file is provided
    if (!file_exists($this->privateKey) || !is_file($this->privateKey)) {
      throw new Exception('Private key does not exist');
    }
    // Create header, claim and signature
    $header = array(
      'alg' => 'RS256',
      'typ' => 'JWT',
    );
    $t = time();
    $params = array(
      'iss' => $this->email,
      'scope' => GoogleOauth::SCOPE_URL,
      'aud' => GoogleOauth::TOKEN_URL,
      'exp' => $t + self::MAX_LIFETIME_SECONDS,
      'iat' => $t,
    );
    $encodings = array(
      base64_encode(json_encode($header)),
      base64_encode(json_encode($params)),
    );
    // Compute Signature
    $input = implode('.', $encodings);
    $certs = array();
    $pkcs12 = file_get_contents($this->privateKey);
    if (!openssl_pkcs12_read($pkcs12, $certs, $this->password)) {
      throw new Exception('Could not parse .p12 file');
    }
    if (!isset($certs['pkey'])) {
      throw new Exception('Could not find private key in .p12 file');
    }
    $keyId = openssl_pkey_get_private($certs['pkey']);
    if (!openssl_sign($input, $sig, $keyId, 'sha256')) {
      throw new Exception('Could not sign data');
    }
    // Generate JWT
    $encodings[] = base64_encode($sig);
    $jwt = implode('.', $encodings);
    return $jwt;
  }
}




/**
 * Send data with curl
 *
 * @link https://github.com/wanze/Google-Analytics-API-PHP/blob/master/GoogleAnalyticsAPI.class.php
 * @copyright {@link https://github.com/wanze/Google-Analytics-API-PHP}
 */
class Http {
  /**
   * Send http requests with curl
   *
   * @access public
   * @static
   * @param mixed $url The url to send data
   * @param array $params (default: array()) Array with key/value pairs to send
   * @param bool $post (default: false) True when sending with POST
   */
  public static function curl($url, $params=array(), $type='GET') {
    if (empty($url)) return false;

    $post = ! in_array( $type, array('GET', 'DELETE') ); 
    $token = false;

    //print_r($params); print_r($type);

    foreach ( $params as $key => $value )
      if ( $value === NULL )
        unset( $params[ $key ] );

    if (!$post && !empty($params)) 
      $url = $url . "?" . http_build_query($params);
    elseif ( isset( $params['access_token'] ) )
    {
      $token = $params['access_token'];
      $url = $url . "?" . http_build_query(array( 'access_token' => $token ));
      unset( $params['access_token'] );
    }
    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);

    if ( $post )
    {
      if ( $token )
      {
        $data = json_encode($params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      }
      else
      {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
      }
    }

    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_VERBOSE, true);
    $data = curl_exec($curl);
    $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    // Add the status code to the json data, useful for error-checking
    $data = preg_replace('/^{/', '{"http_code":'.$http_code.',', $data);
    curl_close($curl);

    if ( ! in_array( $http_code, array(200, 204) ) )
      throw new Exception("HTTP response code is $http_code for $type request to URL $url. The returned data is $data \n Paraleters were: ".print_r($params, true));

    return $data;
  }
}




/**
 * Request to Google Calendar API v3 Class 
 * @package PHPCalendarTool
 */
class Request
{

  /**
   * Endpoint to Google Calendar v3 API
   * @const ENDPOINT string
   */
  const ENDPOINT = "https://www.googleapis.com/calendar/v3/";

  /**
   * Type of request
   * @param $type string
   */
  public $type = "GET";

  /**
   * Request data
   * @param $data array
   */
  public $data;

  /**
   * URL of request
   * @param $url string
   */
  public $url;

  /**
   * Perform the request
   *
   * @return object Request response
   */
  public function perform()
  {
    $token = Token::get();
    $url = self::ENDPOINT . $this->url;
    $this->data['access_token'] = $token;
    $data = Http::curl( $url, $this->data, $this->type );
    return json_decode( $data );
  }

}





/**
 * Google Calendar API v3 Resource Class 
 * @package PHPCalendarTool
 */
abstract class Resource
{

  /**
   * Contains data of the resource
   * @var $data
   */
  public $data = array(); 

  /**
   * Contains changed properties data of the resource.
   * Used to provide information what data should be updated
   * @var $changed_properties
   */
  private $changed_properties = array(); 

  /**
   * Required parmeters that need to be submitted upon update request
   * @var $required_parameters
   */
  public $required_parameters = array(
    'PUT'  => array(),
    'POST' => array(),
  );

  public function __set($key, $value)
  {
    // Store field name
    // We will need it later if we submit changes to Google Calendar
    $this->changed_properties[] = $key;
    $this->changed_properties = array_unique( $this->changed_properties );
    // Set the value
    $this->data[ $key ] = $value;
  }

  public function __get($key)
  {
    if ( isset( $this->data[ $key ] ) )
      return $this->data[ $key ];
    return NULL;
  }

  /**
   * Creates a new resource.
   * If ID is set, then also fetches data from Google.
   *
   * @param string|int $id
   * Resource ID
   */
  public function __construct( $id = NULL )
  {
    if ( ! $id )
      return;
    $this->id = $id;
    $this->get();
  }

  /**
   * Set resource data from array
   */
  public function set( $request )
  {
    foreach ( $request as $key => $value )
      $this->data[ $key ] = $value;
  }

  /**
   * Perform request to Google API
   *
   * @param array $options
   * Additional options for the request
   *
   * @return mixed Data from Google API
   */
  private function request( $options = array(), $type = 'GET' )
  {
    $request = new Request();
    $request->type = $type;
    $request->data = $options;
    $request->url = $this->endpoint();
    if ( $this->id )
      $this->url .= $this->id;
    return $request->perform();
  }

  /**
   * Get all available resources from list
   * @return array
   */
  public static function all()
  {
    $class = get_called_class();
    $class .= 'List';
    return $class::getAll();
  }

  /**
   * Get request data and set it to the current resource object
   *
   * @param array $options
   * Additional options for the request
   *
   * @return mixed
   */
  public function get( $options = array() )
  {
    $request = $this->request( $options );
    $this->set( $request );
    return $request;
  }

  /**
   * Save data
   */
  public function save()
  {
    $type = ( $this->id ? 'PUT' : 'POST' );
    $options = array();
    $properties = $this->changed_properties;
    $properties = array_merge( $properties, $this->required_parameters[$type] );
    //print_r( $properties ); exit;
    foreach ( $properties as $key )
      $options[$key] = $this->data[ $key ];
    $request = $this->request( $options, $type );
    $this->set( $request );
  }

  public function delete()
  {
    $request = $this->request( array(), 'DELETE' );
  }

  /**
   * Endpoint to get the list
   * @return string
   */
  public function endpoint()
  {
    throw new Exception("Endpoint needs to be set in the parent class");
  }

  /**
   * Convert $id to Resource object
   * If $id parameter is already a resource, then just return it.
   *
   * @param int $id Resource or Resource ID
   *
   * @return Resource
   */
  public static function find( $id )
  {
    $class = get_called_class();
    if ( $id instanceof $class )
      return $id;
    return new $class( $id );
  }

}





/**
 * Google Calendar API v3 Resource List Class 
 * @extends Resource
 * @package PHPCalendarTool
 */
abstract class ResourceList extends Resource
{

  /**
   * Get what is the class of list items
   *
   * @return string
   */
  private function getType()
  {
    $class = get_class( $this );
    return str_replace( 'List', '', $class );
  }

  /**
   * Return list of resources
   *
   * @param array $filter
   * Filter options
   *
   * @return array List of resources
   */
  public function getList( $filter = array() )
  {
    $items = array();

    while ( true )
    {
      $response = $this->get( $filter );
      $items = array_merge( $items, $response->items );
      if ( ! isset( $response->nextPageToken ) )
        break;
      $token = $response->nextPageToken;
      if ( ! $token )
        break;
      $filter['pageToken'] = $token;
    }

    $resources = array();
    $resource_class = $this->getType();

    foreach ( $items as $item )
    {
      $resource = new $resource_class();
      $resource->set( $item );
      $resources[] = $resource;
    }

    return $resources;
  }

  /**
   * Just a shorter alias to {@link ResourceList::getList()}
   */
  public static function getAll()
  {
    $class = get_called_class();
    $list = new $class();
    return $list->getList();
  }

}





/**
 * Abstract Class to get access token
 * @package PHPCalendarTool
 */
abstract class Token
{
  /**
   * Get access token.
   * If it does not exist, or if it is not found, then re-create it.
   *
   * @return string Token
   */
  public static function get()
  {
    global $service_account_name, $client_id, $key_file_location;

    $tmp_file = sys_get_temp_dir() . '/access-token-' . md5( 
        $service_account_name . 
        $client_id . 
        $key_file_location 
      )
    ;

    try
    {
      // Try to get the last used token
      if ( !file_exists( $tmp_file ) )
        throw new Exception("Temporary file $tmp_file does not exist.");

      $data = @file_get_contents( $tmp_file );
      if ( !$data )
        throw new Exception("Temporary file $tmp_file is empty");

      $data = @unserialize( $data );
      if ( !$data )
        throw new Exception("Data could not be unserialized. Maybe the file is corrupt.");

      $token = $data['access_token'];

      if ( ! $token )
        throw new Exception("Token is empty.");

      $expires = $data['expires_in'];
      $created = $data['created_at'];

      // Check if token has expired
      if ( time() - $created > $expires - 60 )
        throw new Exception("Token has expired.");

      // All good. We can use the old token.
      return $token;
    }
    catch (Exception $e)
    {
    }

    // Something went wrong
    // We need to generate a new token

    if ( ! file_exists( $key_file_location ) )
      throw new Exception( $key_file_location . " file was not found." );

    if ( ! @file_get_contents( $key_file_location ) )
      throw new Exception( $key_file_location . " file is not readable." );

    // Set up Authorization service
    $auth = new GoogleOauthService();
    $auth->setClientId( $client_id );
    $auth->setEmail( $service_account_name );
    $auth->setPrivateKey( $key_file_location );

    $token = $auth->getAccessToken();

    $token['created_at'] = time();
    $data = serialize( $token );

    @file_put_contents( $tmp_file, $data );

    return $token['access_token'];

  }

}


}
