<?php

namespace PHPCalendar;

/**
 * Google Calendar API v3 Resource List Class 
 * @extends Resource
 * @package PHPCalendarTool
 */
abstract class ResourceList extends Resource
{

  /**
   * Get what is the class of list items
   *
   * @return string
   */
  private function getType()
  {
    $class = get_class( $this );
    return str_replace( 'List', '', $class );
  }

  /**
   * Return list of resources
   *
   * @param array $filter
   * Filter options
   *
   * @return array List of resources
   */
  public function getList( $filter = array() )
  {
    $items = array();

    while ( true )
    {
      $response = $this->get( $filter );
      $items = array_merge( $items, $response->items );
      if ( ! isset( $response->nextPageToken ) )
        break;
      $token = $response->nextPageToken;
      if ( ! $token )
        break;
      $filter['pageToken'] = $token;
    }

    $resources = array();
    $resource_class = $this->getType();

    foreach ( $items as $item )
    {
      $resource = new $resource_class();
      $resource->set( $item );
      $resources[] = $resource;
    }

    return $resources;
  }

  /**
   * Just a shorter alias to {@link ResourceList::getList()}
   */
  public static function getAll()
  {
    $class = get_called_class();
    $list = new $class();
    return $list->getList();
  }

}

