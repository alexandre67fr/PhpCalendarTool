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

  public $required_parameters = array(
    'PUT'  => array('end', 'start'),
    'POST' => array('end', 'start'),
  );

  public function __construct( $id = NULL, $calendar = NULL )
  {
    $this->calendar = $calendar;
    parent::__construct( $id );
  }

  public function __get($key)
  {
    $value = parent::__get( $key );
    if ( in_array( $key, array('start', 'end') ) )
    {
      $formatted = ( isset( $this->data[$key]->dateTime ) ? $this->data[$key]->dateTime : $this->data[$key]->date );
      $value = strtotime( $formatted );
    }
    return $value;
  }

  public function __set($key, $value)
  {
    if ( in_array( $key, array('start', 'end') ) )
    {
      if ( !isset( $this->data[ $key ] ) )
        $this->data[ $key ] = new \stdClass();

      $ts = DateTime::timestamp( $value );
      if ( $ts )
      {
        if ( preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) )
        {
          $value = date('Y-m-d', $ts);
          $this->data[$key]->date = $value;
          unset( $this->data[$key]->dateTime );
        }
        else
        {
          $formatted = DateTime::date3339( $ts );
          $this->data[$key]->dateTime = $formatted;
        }
      }
      return;
    }
    return parent::__set($key, $value);
  }

  public function endpoint()
  {
    $url = $this->calendar->endpoint() . "/events";
    if ( ! $this->id )
      return $url;
    return $url . "/" . $this->id;
  }

  public static function find( $id, $calendar=NULL )
  {
    $class = get_called_class();
    if ( $id instanceof $class )
      return $id;
    return new $class( $id, $calendar );
  }

}

