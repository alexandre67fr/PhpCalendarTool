<?php

global $throw_exceptions;
$throw_exceptions = false;
$throw_exceptions = true;

class CalendarTest extends PHPUnit_Framework_TestCase
{

  public static $calendar;

  public static function getCalendar()
  {
    if ( self::$calendar )
      return self::$calendar;
    $calendars = PHPCalendar\Calendar::all();
    self::$calendar = array_shift( $calendars );
    return self::$calendar;
  }

  public function testCanGetCalendars()
  {
    $calendars = PHPCalendar\Calendar::all();
    $this->assertEquals( 1, count($calendars) );
  }

  public function testCanDeleteEvents()
  {
    $cal = self::getCalendar();
    $cal->deleteAllEvents();
    $events = $cal->events();
    $this->assertEquals( 0, count($events) );
  }

  /**
   * @dataProvider createEventData
   */
  public function testCreate( $heading = NULL, $location = NULL, $description = NULL, $start_datetime = NULL, $end_datetime = NULL )
  {
    $cal = self::getCalendar();

    $result = CreateCalendarEvent( $cal, $heading, $location, $description, $start_datetime, $end_datetime );
    $this->assertTrue( $result );
  }

  /**
   * @dataProvider createEventData
   */
  public function testUpdateEvents( $heading = NULL, $location = NULL, $description = NULL, $start_datetime = NULL, $end_datetime = NULL )
  {
    $cal = self::getCalendar();
    $event = new PHPCalendar\Event( NULL, $cal );
    $event->save();

    $result = SetCalendarEvent( $cal, $event->id, $heading, $location, $description, $start_datetime, $end_datetime );
    $this->assertTrue( $result );
  }

  public function createEventData()
  {
    return array(
      array(),
      array( 'Title 1' ),
      array( 'Title 2', 'Location 2' ),
      array( 'Title 3', 'Location 3', 'Description 3' ),
      array( 'Title 4', 'Location 4', 'Description 4', time() + 60 * 60 ),
      array( 'Title 5', 'Location 5', 'Description 5', time() + 60 * 60, time() + 60 * 60 * 2 ),
      array( 'Title 6', 'Location 6', 'Description 6', date('Y-m-d').' 11:00' ),
      array( 'Title 7', 'Location 7', 'Description 7', date('Y-m-d') ),
      array( 'Title 8', 'Location 8', 'Description 8', date('Y-m-d', time() + 60 * 60 * 24)),
    );
  }

  public function testNumberofEvents()
  {
    $cal = self::getCalendar();
    $this->assertEquals( count( $this->createEventData() ) * 2, count( $cal->events() ) );
  }

  public function createGetEventsData()
  {
    return array(
      array(
        array(
          array( NULL, NULL, NULL, '2015-02-11', NULL ), 
        ),
        '2015-02-10',
        '2015-02-12',
        1,
      )
    );
  }

  /**
   * @dataProvider createGetEventsData
   */
  public function testGetEvents($events, $start, $end, $count)
  {
    $cal = self::getCalendar();
    $cal->deleteAllEvents();

    foreach ($events as $event)
    {
      $result = CreateCalendarEvent($cal, $event[0], $event[1], $event[2], $event[3], $event[4]);
      $this->assertTrue( $result );
    }

    $found = GetEventList($start, $end);
    //print_r($found);
    $this->assertEquals( $count, count( $found ) );
  }

}
