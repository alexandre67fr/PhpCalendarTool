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
//print_r( $events );
//echo count($events);

//$events = GetEventList();
//$events = GetEventList('2015-02-11 00:00', '2015-02-11', $calendars);
//print_r( $events );
//SetCalendarEvent('alexandre67fr@gmail.com', '43v0gkvta5dfknr92omt15mvmo', date('Y:m:d H:i:s'), 'location', 'description', date('Y-m-d'), date('Y-m-d'));
//echo count($events) . "\n";
var_dump( CreateCalendarEvent($calendars[0], date('Y:m:d H:i:s'), 'location 2', 'description 2', time() + 60 * 60 * 2) );
