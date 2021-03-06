<?php

namespace Econemon\Bootsy\DatabaseBundle\Form\Type;

use Econemon\Bootsy\ApplicationBundle\Form\BaseForm;
use Econemon\Bootsy\DatabaseBundle\Form\Model\ConfigFormData;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigForm extends BaseForm
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $this->addFields($builder, array(
      'driver' => 'database driver',
      'name' => 'database name',
      'host' => 'database host',
      'user' => 'database user',
    ));

    $this->addFields($builder, array('newPassword' => 'new password', 'newPasswordRepeat' => 'new password (repeat)'), 'password', array('required' => FALSE));
  }

  /**
   * Returns the name of this type.
   *
   * @return string The name of this type
   */
  public function getName()
  {
    return 'db_config';
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => ConfigFormData::getClassName(), // this makes the class visible to the IDE, so it is refactorable, which a string literal would not be.
    ));
  }
}