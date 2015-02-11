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
          unset( $this->data[$key]->date );
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

  public function save()
  {
    // Set start and end time if they were not set
    // They are required by Google API
    if ( ! isset( $this->data[ 'start' ] ) )
      $this->data[ 'start' ] = new \stdClass();

    if (
      ! isset( $this->data['start']->date )
      and
      ! isset( $this->data['start']->dateTime )
    )
      $this->data['start']->date = date('Y-m-d');

    if ( ! isset( $this->data['end'] ) or ( $this->start > $this->end ) )
    {
      $this->data['end'] = clone $this->data['start'];
      if ( $this->data['end']->date )
        $this->data['end']->date = date('Y-m-d', strtotime( $this->data['start']->date ) + 24 * 60 * 60 + 1);
    }

    if (
      ! isset( $this->data['end']->date )
      and
      ! isset( $this->data['end']->dateTime )
    )
      $this->data['end'] = clone $this->data['start'];

    return parent::save();
  }

  public static function find( $id, $calendar=NULL )
  {
    $class = get_called_class();
    if ( $id instanceof $class )
      return $id;
    return new $class( $id, $calendar );
  }

}

