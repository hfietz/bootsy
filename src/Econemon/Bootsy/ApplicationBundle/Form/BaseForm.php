<?php

namespace Econemon\Bootsy\ApplicationBundle\Form;

use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class BaseForm extends AbstractType
{
  /**
   * @var TranslatorInterface
   */
  protected $translator;

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

  /**
   * @param \Symfony\Component\Translation\TranslatorInterface $translator
   */
  public function setTranslator($translator)
  {
    $this->translator = $translator;
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
   * @param string $name
   * @param string $label
   * @param DateTime|null $default the default date to render, NULL means current date, FALSE means no default value
   * @param string $translationDomain
   * @param array $extraOptions
   */
  protected function addDateField(FormBuilderInterface $builder, $name, $label, $default = NULL, $translationDomain, $extraOptions = array())
  {
    $format = 'y-M-d';

    if (NULL === $default) {
      $default = new DateTime();
    }

    if (is_a($default, 'DateTime')) {
      $empty_value = $default->format($format);
    } else {
      $empty_value = '';
    }

    $standardOptions = array(
      'label' => $label,
      'translation_domain' => $translationDomain,
      'widget' => 'single_text',
      'format' => $format,
      'empty_value' => $empty_value,
      'attr' => array('class' => 'date-field'),
    );
    $options = array_merge($standardOptions, $extraOptions);

    $builder->add($name, 'date', $options);
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