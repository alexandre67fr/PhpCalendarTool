<?php

$folder = __DIR__ . "/../src/";
$folder_classes = $folder . "classes/";

$classes = array_diff(scandir( $folder_classes ), array('..', '.'));

$file = $folder . "calendar_tool.php";

$data = "\n\nnamespace\n{\n";

$data .= file_get_contents( $file );

$data .= "\n}\n";
$data .= "\n\nnamespace PHPCalendar\n{\n";

foreach ( $classes as $class )
{
  $php = file_get_contents( $folder_classes . $class );
  $php = str_replace('namespace PHPCalendar;', "", $php);
  $data .= $php;
}

$data .= "\n}\n";

$data = str_replace( '<' . '?php', '', $data );
$data = '<' . '?php' . $data;

file_put_contents( __DIR__ . "/../build/" . basename( $file ), $data );
