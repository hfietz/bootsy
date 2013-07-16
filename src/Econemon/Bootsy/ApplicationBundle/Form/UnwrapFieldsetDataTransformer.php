<?php

namespace Econemon\Bootsy\ApplicationBundle\Form;

use Symfony\Component\Form\DataTransformerInterface;

class UnwrapFieldsetDataTransformer implements DataTransformerInterface
{
  protected $fieldsets = array();

  public function addSet($name, array $fields)
  {
    $this->fieldsets[$name] = $fields;
  }

  /**
   * @inheritdoc
   */
  public function transform($value)
  {
    foreach ($this->fieldsets as $fieldset => $fields) {
      $value->{$fieldset} = array();
      foreach ($fields as $field) {
        $value->{$fieldset}[$field] = $value->{$field};
      }
    }

    return $value;
  }

  /**
   * @inheritdoc
   */
  public function reverseTransform($value)
  {
    foreach ($this->fieldsets as $fieldset => $fields) {
      foreach ($fields as $field) {
        $value->{$field} = $value->{$fieldset}[$field];
      }
      unset($value->{$fieldset});
    }

    return $value;
  }

}