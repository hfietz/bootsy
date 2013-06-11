<?php

namespace Econemon\Bootsy\DatabaseBundle\Service;

class Hydrator
{
  public static function hydrate($object, $row, $mapping = array())
  {
    foreach ($row as $name => $value) {
      $name = self::getDefaultMappedColumnName($name);
      if (FALSE === self::mapTo($object, $name, $value) && array_key_exists($name, $mapping)) {
        self::mapTo($object, $mapping[$name], $value);
      }
    }

    return $object;
  }

  public static function export($object, $mapping = array())
  {
    $export = array();

    foreach ($mapping as $column => $property) {
      $getter = self::getGetterForName($property);
      if (method_exists($object, $getter)) {
        $export[$column] = call_user_func(array($object, $getter));
      } else if (property_exists($object, $property)) {
        $export[$column] = $object->{$property};
      }
    }

    return $export;
  }

  public static function mapTo($object, $name, $value)
  {
    $hasSetValue = FALSE;

    if (is_object($object)) {
      $setter = self::getSetterForName($name);
      if (method_exists($object, $setter)) {
        call_user_func(array($object, $setter), $value);
        $hasSetValue = TRUE;
      } else if (property_exists($object, $name)){
        $object->{$name} = $value;
        $hasSetValue = TRUE;
      }
    }

    return $hasSetValue;
  }

  public static function getSetterForName($name)
  {
    // We assume camel-casing
    return 'set' . ucfirst($name);
  }

  public static function getGetterForName($name)
  {
    // We assume camel-casing
    return 'get' . ucfirst($name);
  }

  /**
   * @param $name
   * @return mixed
   */
  public static function getDefaultMappedColumnName($name)
  {
    $name = preg_replace_callback('/_([a-z])/', function ($matches) {
      return strtoupper($matches[1]);
    }, strtolower($name));
    return $name;
  }
}