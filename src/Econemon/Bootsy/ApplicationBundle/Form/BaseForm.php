<?php

namespace Econemon\Bootsy\ApplicationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class BaseForm extends AbstractType
{
  /**
   * @param FormBuilderInterface $builder
   * @param array $fields
   * @param string $type
   * @param array $options
   * @return void
   */
  public function addFields(FormBuilderInterface $builder, $fields, $type = 'text', $options = array())
  {
    foreach ($fields as $name => $label) {
      if (is_numeric($name)) {
        $name = $label;
        $label = $this->createAutoLabelFromName($name);
      }
      $builder->add($name, $type, array_merge($options, array('label' => $label)));
    }
  }

  /**
   * @param $name
   * @return string
   */
  protected function createAutoLabelFromName($name)
  {
    return ucfirst(strtolower(str_replace('_', '', $name)));
  }
}