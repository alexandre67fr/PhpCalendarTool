<?php

namespace PHPCalendar;

/**
 * Abstract Class to get access token
 * @package PHPCalendarTool
 */
abstract class Token
{
  /**
   * Get access token.
   * If it does not exist, or if it is not found, then re-create it.
   *
   * @return string Token
   */
  public static function get()
  {
    global $service_account_name, $client_id, $key_file_location;

    $tmp_file = sys_get_temp_dir() . '/access-token-' . md5( 
        $service_account_name . 
        $client_id . 
        $key_file_location 
      )
    ;

    try
    {
      // Try to get the last used token
      if ( !file_exists( $tmp_file ) )
        throw new Exception("Temporary file $tmp_file does not exist.");

      $data = @file_get_contents( $tmp_file );
      if ( !$data )
        throw new Exception("Temporary file $tmp_file is empty");

      $data = @unserialize( $data );
      if ( !$data )
        throw new Exception("Data could not be unserialized. Maybe the file is corrupt.");

      $token = $data['access_token'];

      if ( ! $token )
        throw new Exception("Token is empty.");

      $expires = $data['expires_in'];
      $created = $data['created_at'];

      // Check if token has expired
      if ( time() - $created > $expires - 60 )
        throw new Exception("Token has expired.");

      // All good. We can use the old token.
      return $token;
    }
    catch (Exception $e)
    {
    }

    // Something went wrong
    // We need to generate a new token

    if ( ! file_exists( $key_file_location ) )
      throw new Exception( $key_file_location . " file was not found." );

    if ( ! @file_get_contents( $key_file_location ) )
      throw new Exception( $key_file_location . " file is not readable." );

    // Set up Authorization service
    $auth = new GoogleOauthService();
    $auth->setClientId( $client_id );
    $auth->setEmail( $service_account_name );
    $auth->setPrivateKey( $key_file_location );

    $token = $auth->getAccessToken();

    $token['created_at'] = time();
    $data = serialize( $token );

    @file_put_contents( $tmp_file, $data );

    return $token['access_token'];

  }

}

