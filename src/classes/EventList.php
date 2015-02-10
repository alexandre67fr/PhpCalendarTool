<?php

namespace PHPCalendar;

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

