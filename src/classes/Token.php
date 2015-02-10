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
   * TODO: refresh token
   * TODO: check old token validity
   *
   * @return string Token
   */
  public static function get()
  {
    $tmp_file = PHPCALTOOL_TMP_FILE;

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

    if ( ! file_exists( PHPCALTOOL_KEY_FILE_LOCATION ) )
      throw new Exception( PHPCALTOOL_KEY_FILE_LOCATION . " file was not found." );

    if ( ! @file_get_contents( PHPCALTOOL_KEY_FILE_LOCATION ) )
      throw new Exception( PHPCALTOOL_KEY_FILE_LOCATION . " file is not readable." );

    // Set up Authorization service
    $auth = new GoogleOauthService();
    $auth->setClientId( PHPCALTOOL_CLIENT_ID );
    $auth->setEmail( PHPCALTOOL_SERVICE_ACCOUNT_NAME );
    $auth->setPrivateKey( PHPCALTOOL_KEY_FILE_LOCATION );

    $token = $auth->getAccessToken();

    $token['created_at'] = time();
    $data = serialize( $token );

    @file_put_contents( $tmp_file, $data );

    return $token['access_token'];

  }

}

