<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include __DIR__ . '/../src/calendar_tool.php';
$throw_exceptions = false;

//GetCalendarEventList();

$calendars = PHPCalendar\Calendar::all();
//print_r( $calendars );

//$calendar = array_shift( $calendars );
//$events = $calendar->events( array(
  //'maxResults' => 10,
//) );
//echo count($events);

//$events = GetEventList();
//$events = GetEventList('2015-02-11 00:00', '2015-02-11', $calendars);
//SetCalendarEvent('alexandre67fr@gmail.com', '43v0gkvta5dfknr92omt15mvmo', date('Y:m:d H:i:s'), 'location', 'description', date('Y-m-d'), date('Y-m-d'));
//echo count($events) . "\n";
//var_dump( CreateCalendarEvent($calendars[0], date('Y:m:d H:i:s'), 'location 2', 'description 2', '2015-02-13' ) );


// Create 'My Test Event' on February, 11th, 2015, from 11:00 to 12:00
//var_dump( CreateCalendarEvent($calendars[0], 'My Test Event', NULL, NULL, '2015-02-11 11:00', '2015-02-11 12:00') );
// Create 'My Second Test Event' on February, 11th, 2015, for the whole day
//var_dump( CreateCalendarEvent($calendars[0], 'My Second Test Event', NULL, NULL, '2015-02-11') );
//
print_r( GetEventList(NULL, NULL) );
