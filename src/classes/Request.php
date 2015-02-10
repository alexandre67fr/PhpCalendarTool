<?php

namespace PHPCalendar;

/**
 * Request to Google Calendar API v3 Class 
 * @package PHPCalendarTool
 */
class Request
{

  /**
   * Perform the request
   *
   * @return object Request response
   */
  public function perform()
  {
    $token = Token::get();
    echo file_get_contents("https://www.googleapis.com/calendar/v3/users/me/calendarList?access_token=$token");
  }

}

