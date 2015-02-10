<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include __DIR__ . '/../src/calendar_tool.php';

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
//$events = GetEventList('2015-02-09 10:01', '2015-02-09 10:02', 'alexandre67fr@gmail.com');
SetCalendarEvent('alexandre67fr@gmail.com', '43v0gkvta5dfknr92omt15mvmo', time());
//print_r( $events );
//echo count($events) . "\n";
