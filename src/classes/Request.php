<?php

namespace PHPCalendar;

/**
 * Request to Google Calendar API v3 Class 
 * @package PHPCalendarTool
 */
class Request
{

  /**
   * Endpoint to Google Calendar v3 API
   * @const ENDPOINT string
   */
  const ENDPOINT = "https://www.googleapis.com/calendar/v3/";

  /**
   * Type of request
   * @param $type string
   */
  public $type;

  /**
   * Request data
   * @param $data array
   */
  public $data;

  /**
   * URL of request
   * @param $url string
   */
  public $url;

  /**
   * Perform the request
   *
   * @return object Request response
   */
  public function perform()
  {
    $token = Token::get();
    $url = self::ENDPOINT . $this->url;
    $this->data['access_token'] = $token;
    $data = Http::curl( $url, $this->data, false );
    return json_decode( $data );
  }

}

