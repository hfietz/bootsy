<?php

namespace Econemon\Bootsy\ApplicationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldsetType extends AbstractType
{
  const NAME = 'fieldset';

  const DEFAULT_TRANSLATION_DOMAIN = 'bootsy';

  public function buildView(FormView $view, FormInterface $form, array $options)
  {
    parent::buildView($view, $form, $options);

    $view->vars = array_replace($view->vars, array(
      'legend' => array_key_exists('legend', $options) ? $options['legend'] : '',
    ));
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    parent::setDefaultOptions($resolver);

    $resolver->setRequired(array('legend'));

    $resolver->setDefaults(array(
      'translation_domain' => self::DEFAULT_TRANSLATION_DOMAIN,
      'transitive' => TRUE,
    ));
  }

  /**
   * Returns the name of this type.
   *
   * @return string The name of this type
   */
  public function getName()
  {
    return self::NAME;
  }

  public function getParent()
  {
    return 'form';
  }
}