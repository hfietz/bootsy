<?php

namespace Econemon\Bootsy\ApplicationBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FieldsetBuilder
{
  /**
   * @var FormBuilderInterface
   */
  protected $fieldset;

  /**
   * @var string
   */
  protected $fieldsetName = NULL;

  /**
   * @var array
   */
  protected $fieldsetOptions = array();

  /**
   * @var array
   */
  protected $fields = array();

  /**
   * @var FormBuilderInterface
   */
  protected $builder;

  /**
   * @var TranslatorInterface
   */
  protected $translator = NULL;

  /**
   * @var string
   */
  protected $translationDomain = 'bootsy';

  /**
   * @var bool
   */
  protected $isBuilt = FALSE;

  public function __construct(FormBuilderInterface $builder, $fieldsetName, $legend)
  {
    $this->builder = $builder;
    $this->fieldsetName = $fieldsetName;
    $this->setOption('legend', $legend);
    $this->fields = array();
  }

  /**
   * @param string $name
   * @param string $label
   * @param string $type
   * @param bool $required
   * @param array $options
   * @return $this
   */
  public function addField($name, $label, $type = 'text', $required = FALSE, $options = array())
  {
    $fieldOptions = array_merge($options, array(
      'label' => $label,
      'required' => $required,
    ));

    $this->fields[$name] = array($type, $fieldOptions);

    return $this;
  }

  public function setTransitive($newValue = TRUE)
  {
    return $this->setOption('transitive', $newValue);
  }

  public function setTranslator(TranslatorInterface $t, $dom = 'bootsy')
  {
    $this->translator = $t;
    $this->translationDomain = $dom;
    $this->setOption('translation_domain', $this->translationDomain);

    return $this;
  }

  /**
   * @return FormBuilderInterface
   */
  public function buildAndAdd()
  {
    if (FALSE === $this->isBuilt) {
      $this->translateOption('legend');
      $this->fieldset = $this->builder->create($this->fieldsetName, 'fieldset', $this->fieldsetOptions);

      foreach ($this->fields as $name => $definition) {
        list($type, $fieldOptions) = $definition;

        if (!array_key_exists('translation_domain', $fieldOptions)) {
          $fieldOptions['translation_domain'] = $this->fieldset->getOption('translation_domain');
        }

        $this->translateOption('label', array(), $fieldOptions);

        $field = $this->fieldset->create($name, $type, $fieldOptions);

        $this->fieldset->add($field);
      }

      $this->builder->add($this->fieldset);

      if ($this->fieldset->getOption('transitive')) {
        $transformer = new UnwrapFieldsetDataTransformer();
        $transformer->addSet($this->fieldsetName, array_keys($this->fields));
        $this->builder->addViewTransformer($transformer);
      }
      $this->isBuilt = TRUE;
    }
    return $this->fieldset;
  }

  /**
   * @param string $name
   * @param mixed $value
   * @return $this
   */
  public function setOption($name, $value, array &$target = NULL)
  {
    if (NULL === $target) {
      $target =& $this->fieldsetOptions;
    }

    $target[$name] = $value;

    return $this;
  }

  /**
   * @param string $name
   * @param bool $found
   * @return mixed
   */
  public function getOption($name, &$found = NULL, array $target = NULL)
  {
    $value = NULL;

    if (NULL === $target) {
      $target = $this->fieldsetOptions;
    }

    $target = $this->fieldsetOptions;
    if (array_key_exists($name, $target)) {
      $value = $target[$name];
      $found = TRUE;
    } else {
      $found = FALSE;
    }

    return $value;
  }

  /**
   * @param $name
   */
  public function translateOption($name, $params = array(), $target = NULL)
  {
    $option = $this->getOption($name, $found, $target);

    if ($found && NULL !== $this->translator) {
      $this->setOption($name, $this->translator->trans($option, $params, $this->translationDomain), $target);
    }
  }
}