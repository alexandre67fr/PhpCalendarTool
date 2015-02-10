<?php

namespace PHPCalendar;

/**
 * Abstract Auth class
 *
 * @link https://github.com/wanze/Google-Analytics-API-PHP/blob/master/GoogleAnalyticsAPI.class.php
 * @copyright {@link https://github.com/wanze/Google-Analytics-API-PHP}
 *
 */
abstract class GoogleOauth {

  const TOKEN_URL = 'https://accounts.google.com/o/oauth2/token';
  const SCOPE_URL = 'https://www.googleapis.com/auth/calendar';

  protected $assoc = true;
  protected $clientId = '';

  public function __set($key, $value)
  {
    $this->{$key} = $value;
  }

  public function setClientId($id)
  {
    $this->clientId = $id;
  }

  public function returnObjects($bool)
  {
     $this->assoc = !$bool;
  }

  /**
   * To be implemented by the subclasses
   *
   */
  public function getAccessToken($data=null) {}
}
