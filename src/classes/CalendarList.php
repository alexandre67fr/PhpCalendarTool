<?php

namespace PHPCalendar;

/**
 * Calendar Event Class 
 * @extends ResourceList
 * @package PHPCalendarTool
 */
class CalendarList extends ResourceList
{

  public function endpoint()
  {
    return "users/me/calendarList";
  }

}

