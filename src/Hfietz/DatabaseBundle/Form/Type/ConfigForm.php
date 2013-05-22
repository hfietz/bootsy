<?php

namespace Hfietz\DatabaseBundle\Form\Type;

use Hfietz\DatabaseBundle\Form\Model\ConfigFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfigForm extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    foreach (array('driverPrevious', 'namePrevious', 'hostPrevious', 'userPrevious') as $name) {
      $builder->add($name, 'hidden');
    }
    foreach (array('driver', 'name', 'host', 'user') as $name) {
      $builder->add($name, 'text');
    }

    if (!empty($pass)) {
      $builder->add('currentPassword', 'password');
    }
    foreach (array('newPassword', 'newPasswordRepeat') as $name) {
      $builder->add($name, 'password');
    }
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