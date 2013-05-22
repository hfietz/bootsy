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
    $this->addFields($builder, array('driverPrevious', 'namePrevious', 'hostPrevious', 'userPrevious'), 'hidden');

    $this->addLabelledFields($builder, array(
      'driver' => 'database driver',
      'name' => 'database name',
      'host' => 'database host',
      'user' => 'database user',
    ));

    if (!empty($pass)) {
      $this->addLabelledFields($builder, array('currentPassword' =>'current password'), 'password');
    }

    $this->addLabelledFields($builder, array('newPassword' => 'new password', 'newPasswordRepeat' => 'new password (repeat)'), 'password');
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

  /**
   * @param FormBuilderInterface $builder
   * @param $fields
   * @param $type
   * @void
   */
  public function addFields(FormBuilderInterface $builder, $fields, $type = 'text')
  {
    foreach ($fields as $name) {
      $builder->add($name, $type);
    }
  }

  /**
   * @param FormBuilderInterface $builder
   * @param $fields
   * @param $type
   * @return array
   */
  public function addLabelledFields(FormBuilderInterface $builder, $fields, $type = 'text', $options = array())
  {
    foreach ($fields as $name => $label) {
      $builder->add($name, $type, array_merge($options, array('label' => $label)));
    }
  }
}