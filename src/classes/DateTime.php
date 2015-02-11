<?php

namespace PHPCalendar;

abstract class DateTime
{

  /**
   * Format string into a timestamp
   *
   * @param string $string 
   * Input string
   *
   * @return int Timestamp in Unix format
   */
  public static function timestamp( $string )
  {
    date_default_timezone_set('Europe/Paris');
    if ( ! $string )
      return NULL;
    if ( is_numeric( $string ) )
      return intval( $string );
    $ts = strtotime( $string );
    if ( $ts === FALSE )
      throw new Exception("Failed to convert string '$string' into a timestamp.");
    return $ts;
  }

  /**
   * Get date in RFC3339
   * For example used in XML/Atom
   *
   * @param integer $timestamp
   * @return string date in RFC3339
   * @author Boris Korobkov
   */
  public static function date3339( $timestamp = 0 ) {

    if ( $timestamp === NULL )
      return NULL;

    if ( ! $timestamp )
      $timestamp = time();
    
    $date = date('Y-m-d\TH:i:s', $timestamp);

    $matches = array();
    if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) 
      $date .= $matches[1].$matches[2].':'.$matches[3];
    else
      $date .= 'Z';
    
    return $date;
  }

}
