<?php
/**
 * PHP Calendar tool
 *
 * A standalone PHP program that can read, update and create events in Google Calendar
 *
 * @package PHPCalendarTool
 * @author Alexandre S. <alexandre67fr@gmail.com>
 * @version 1.0
 */

/**
 * Settings of the application
 *
 * They can be obtained here in Google Console <https://code.google.com/apis/console>
 * Choose "Credentials" → "OAuth" → "Create new Client ID" → "Service account"
 * @link https://code.google.com/apis/console Google Console
 *
 * After credentials are created, please change the three settings below
 */

/**
 * Google API: Client ID
 * @global PHPCALTOOL_CLIENT_ID string
 */
define('PHPCALTOOL_CLIENT_ID', '1079666599882-jj99t30kk5tgpns5lqqcqioap4k22oho.apps.googleusercontent.com');

/**
 * Google API: Service account name
 * @global PHPCALTOOL_SERVICE_ACCOUNT_NAME string
 */
define('PHPCALTOOL_SERVICE_ACCOUNT_NAME', '1079666599882-jj99t30kk5tgpns5lqqcqioap4k22oho@developer.gserviceaccount.com');

/**
 * Google API: Path to certificate file
 * It should be absolute, or relative to the executed file
 * E.g.: __DIR__ . "/certificate.p12"
 * @global PHPCALTOOL_CLIENT_ID string Path to the certificate file
 */
define('PHPCALTOOL_KEY_FILE_LOCATION', '../keys/certificate.p12');

/**
 * Autoload classes
 * @internal
 */
function phpcalendar_autoloader($class)
{
  $class = str_replace('PHPCalendar\\', '', $class);
  $file = __DIR__ . '/classes/' . $class . '.php';
  @include $file;
}
spl_autoload_register('phpcalendar_autoloader');

/**
 * Gets the list of all events in all calendars, except deleted and events without both start and end datetime
 * It is described {@link https://developers.google.com/google-apps/calendar/v3/reference/events/list#timeMax here} how timestamps act.
 * Bounds are exclusive for an event's start/end time to filter by. They are optional. The default is not to filter by start/end time.
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
 * @reference {@link https://developers.google.com/google-apps/calendar/v3/reference/events/list Events:List() method of Google Calendar API v3}
 *
 * @param datetime|timestamp                  $start_datetime   Date time or timestamp of the oldest returned event. Optional.
 * @param datetime|timestamp                  $end_datetime     Date time or timestamp of the newest returned event. Optional.
 * @param PHPCalendar\Calendar|string         $calendar         Show events only from this calendar. Can be an object or calendar ID. Optional.
 *
 * @return PHPCalendar\Event[] Array of events. Or an empty array if no events were found.
 */
function GetEventList($start_datetime=NULL, $end_datetime=NULL, $calendar=NULL)
{
}

/**
 * Updates a calendar event.
 *
 * If some arguments are supplied as NULL, than the corresponding property will not be changed.
 *
 * @uses PHPCalendar\Event
 * @uses PHPCalendar\Calendar
 * @reference {@link https://developers.google.com/google-apps/calendar/v3/reference/events/patch Events:Patch() method of Google Calendar API v3}
 *
 * @param PHPCalendar\Calendar|string          $calendar         Calendar to which an event belongs to. Can be an object or calendar ID.
 * @param PHPCalendar\Event|string             $event            Event which is being updated. Object or event ID.
 * @param string                               $heading          Title of the event. Optional.
 * @param string                               $location         Location of the event. Optional.
 * @param string                               $description      Description of the event. Optional.
 * @param datetime|timestamp                   $start_datetime   Date time or timestamp when the event starts. Optional.
 * @param datetime|timestamp                   $end_datetime     Date time or timestamp when the event ends. Optional.
 *
 * @return boolean TRUE on success, FALSE on failure.
 *
 */
function SetCalendarEvent($calendar, $event, $heading=NULL, $location=NULL, $description=NULL, $start_datetime=NULL, $end_datetime=NULL)
{
  
}

/**
 * Creates a calendar event.
 *
 * @uses PHPCalendar\Event
 * @uses PHPCalendar\Calendar
 * @reference {@link https://developers.google.com/google-apps/calendar/v3/reference/events/insert Events:Insert() method of Google Calendar API v3}
 *
 * @param PHPCalendar\Calendar|string          $calendar         Calendar where the event is being created. Can be an object or calendar ID.
 * @param string                               $heading          Title of the event. Optional.
 * @param string                               $location         Location of the event. Optional.
 * @param string                               $description      Description of the event. Optional.
 * @param datetime|timestamp                   $start_datetime   Date time or timestamp when the event starts. Optional.
 * @param datetime|timestamp                   $end_datetime     Date time or timestamp when the event ends. Optional.
 *
 * @return boolean TRUE on success, FALSE on failure.
 *
 */
function CreateCalendarEvent($calendar, $heading=NULL, $location=NULL, $description=NULL, $start_datetime=NULL, $end_datetime=NULL)
{
  
}
