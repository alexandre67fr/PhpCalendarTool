<?php

namespace PHPCalendar;

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

  public function __construct( $id = NULL, $calendar = NULL )
  {
    $this->calendar = $calendar;
    parent::__construct( $id );
  }

  /**
   * Convert start date from string to timestamp
   *
   * @return int Start date timestamp
   */
  public function start()
  {
    return strtotime( $this->start->dateTime );
  }

  /**
   * Convert end date from string to timestamp
   *
   * @return int End date timestamp
   */
  public function end()
  {
    return strtotime( $this->end->dateTime );
  }

  public function endpoint()
  {
    return $this->calendar->endpoint() . "/events/" . $this->id;
  }

  public static function find( $id, $calendar=NULL )
  {
    $class = get_called_class();
    if ( $id instanceof $class )
      return $id;
    return new $class( $id, $calendar );
  }

}

