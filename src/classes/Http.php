<?php

namespace PHPCalendar;

/**
 * Send data with curl
 *
 * @link https://github.com/wanze/Google-Analytics-API-PHP/blob/master/GoogleAnalyticsAPI.class.php
 * @copyright {@link https://github.com/wanze/Google-Analytics-API-PHP}
 */
class Http {
  /**
   * Send http requests with curl
   *
   * @access public
   * @static
   * @param mixed $url The url to send data
   * @param array $params (default: array()) Array with key/value pairs to send
   * @param bool $post (default: false) True when sending with POST
   */
  public static function curl($url, $params=array(), $type='GET') {
    if (empty($url)) return false;

    $post = ( $type != 'GET' ); 
    $token = false;

    //print_r($params);

    foreach ( $params as $key => $value )
      if ( $value === NULL )
        unset( $params[ $key ] );

    if (!$post && !empty($params)) 
      $url = $url . "?" . http_build_query($params);
    elseif ( $params['access_token'] )
    {
      $token = $params['access_token'];
      $url = $url . "?" . http_build_query(array( 'access_token' => $token ));
      unset( $params['access_token'] );
    }
    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);

    if ( $post )
    {
      if ( $token )
      {
        $data = json_encode($params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
      }
      else
      {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
      }
    }

    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_VERBOSE, true);
    $data = curl_exec($curl);
    $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    // Add the status code to the json data, useful for error-checking
    $data = preg_replace('/^{/', '{"http_code":'.$http_code.',', $data);
    curl_close($curl);

    if ( $http_code != 200 )
      throw new Exception("HTTP response code is $http_code for request to URL $url. The returned data is $data");

    return $data;
  }
}
