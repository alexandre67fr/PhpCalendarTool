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
 * @global PHPCALTOOL_CLIENT_ID string Path to the certificate file
 */
define('PHPCALTOOL_KEY_FILE_LOCATION', '../keys/certificate.p12');


/**
 * Gets the list of all events in all calendars, except deleted and events without both start and end datetime
 *
 * @uses PHPCalendar\Event
 * @link https://developers.google.com/google-apps/calendar/v3/reference/events/list Events:List method on Google Calendar API v3 Reference
 *
 * @param string|int $ts_oldest Date time or timestamp of the oldest returned event
 * @param string|int $ts_newest Date time or timestamp of the newest returned event
 *
 * @return PHPCalendarEvent[] Array of calendar events, or an empty array if no events were found
 */
function GetEventList($ts_oldest, $ts_newest)
{
  
}
