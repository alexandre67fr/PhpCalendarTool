<?php

namespace PHPCalendar;

/**
 * Google Calendar API v3 Resource Class 
 * @package PHPCalendarTool
 */
abstract class Resource
{

  /**
   * Contains data of the resource
   * @var $data
   */
  public $data = array(); 

  /**
   * Contains changed properties data of the resource.
   * Used to provide information what data should be updated
   * @var $changed_properties
   */
  private $changed_properties = array(); 

  /**
   * Required parmeters that need to be submitted upon update request
   * @var $required_parameters
   */
  public $required_parameters = array(
    'PUT'  => array(),
    'POST' => array(),
  );

  public function __set($key, $value)
  {
    // Store field name
    // We will need it later if we submit changes to Google Calendar
    $this->changed_properties[] = $key;
    $this->changed_properties = array_unique( $this->changed_properties );
    // Set the value
    $this->data[ $key ] = $value;
  }

  public function __get($key)
  {
    if ( isset( $this->data[ $key ] ) )
      return $this->data[ $key ];
    return NULL;
  }

  /**
   * Creates a new resource.
   * If ID is set, then also fetches data from Google.
   *
   * @param string|int $id
   * Resource ID
   */
  public function __construct( $id = NULL )
  {
    if ( ! $id )
      return;
    $this->id = $id;
    $this->get();
  }

  /**
   * Set resource data from array
   */
  public function set( $request )
  {
    foreach ( $request as $key => $value )
      $this->data[ $key ] = $value;
  }

  /**
   * Perform request to Google API
   *
   * @param array $options
   * Additional options for the request
   *
   * @return mixed Data from Google API
   */
  private function request( $options = array(), $type = 'GET' )
  {
    $request = new Request();
    $request->type = $type;
    $request->data = $options;
    $request->url = $this->endpoint();
    if ( $this->id )
      $this->url .= $this->id;
    return $request->perform();
  }

  /**
   * Get all available resources from list
   * @return array
   */
  public static function all()
  {
    $class = get_called_class();
    $class .= 'List';
    return $class::getAll();
  }

  /**
   * Get request data and set it to the current resource object
   *
   * @param array $options
   * Additional options for the request
   *
   * @return mixed
   */
  public function get( $options = array() )
  {
    $request = $this->request( $options );
    $this->set( $request );
    return $request;
  }

  /**
   * Save data
   */
  public function save()
  {
    $type = ( $this->id ? 'PUT' : 'POST' );
    $options = array();
    $properties = $this->changed_properties;
    $properties = array_merge( $properties, $this->required_parameters[$type] );
    //print_r( $properties ); exit;
    foreach ( $properties as $key )
      $options[$key] = $this->data[ $key ];
    $request = $this->request( $options, $type );
    $this->set( $request );
  }

  public function delete()
  {
    $request = $this->request( array(), 'DELETE' );
  }

  /**
   * Endpoint to get the list
   * @return string
   */
  public function endpoint()
  {
    throw new Exception("Endpoint needs to be set in the parent class");
  }

  /**
   * Convert $id to Resource object
   * If $id parameter is already a resource, then just return it.
   *
   * @param int $id Resource or Resource ID
   *
   * @return Resource
   */
  public static function find( $id )
  {
    $class = get_called_class();
    if ( $id instanceof $class )
      return $id;
    return new $class( $id );
  }

}

