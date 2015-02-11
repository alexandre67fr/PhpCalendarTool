<?php
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
    $start_datetime -= 1;
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
        'timeMin' => PHPCalendar\DateTime::date3339( $start_datetime ),
        'timeMax' => PHPCalendar\DateTime::date3339( $end_datetime ),
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
    var_dump($end_datetime);
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
