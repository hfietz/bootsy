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

  protected function addSelect(FormBuilderInterface $builder, $name, $label, $choices, $translationDomain, $extraOptions = array())
  {
    $standardOptions = array(
      'label' => $label,
      'translation_domain' => $translationDomain,
      'expanded' => FALSE,
      'multiple' => FALSE,
      'empty_value' => FALSE,
      'choices' => $choices,
    );
    $options = array_merge($standardOptions, $extraOptions);
    $builder->add($name, 'choice', $options);
  }

  /**
   * @param FormBuilderInterface $builder
   * @param $name
   * @param $label
   * @param $translationDomain
   * @param $type
   */
  protected function addField(FormBuilderInterface $builder, $name, $label, $translationDomain = 'messages', $type = 'text', $extraOptions = array())
  {
    $fieldOptions = array(
      'label' => $label,
      'translation_domain' => $translationDomain,
    );

    $builder->add($name, $type, array_merge($fieldOptions, $extraOptions));
  }

  /**
   * @param FormBuilderInterface $builder
   * @param $name
   * @param $label
   * @param $translationDomain
   */
  protected function addEmailField(FormBuilderInterface $builder, $name, $label, $translationDomain = 'messages', $extraOptions = array())
  {
    $this->addField($builder, $name, $label, $translationDomain, 'email', $extraOptions);
  }
}