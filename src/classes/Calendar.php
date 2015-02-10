<?php

namespace PHPCalendar;

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
    return $list->getList( $filter );
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

