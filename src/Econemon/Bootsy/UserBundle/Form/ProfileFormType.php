<?php

namespace Econemon\Bootsy\UserBundle\Form;

use FOS\UserBundle\Form\Type\ProfileFormType as BaseForm;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileFormType extends BaseForm
{
  const FORM_NAME = 'econemon_bootsy_user_profile';

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    parent::buildForm($builder, $options);

    // Stolen from ChangePasswordFormType
    $builder->add('plainPassword', 'repeated', array(
      'type' => 'password',
      'options' => array('translation_domain' => 'FOSUserBundle'),
      'first_options' => array('label' => 'form.new_password'),
      'second_options' => array('label' => 'form.new_password_confirmation'),
      'invalid_message' => 'fos_user.password.mismatch',
    ));

    $builder->add('name', 'text', $this->standard_options('form.user.name'));
    $builder->add('phone', 'text', $this->standard_options('form.user.phone'));

    $field_options = array_merge($this->standard_options('form.user.newEmail'), array('mapped' => false,));
    $builder->add('newEmail', 'email', $field_options);
  }

  public function getName()
  {
    return self::FORM_NAME;
  }

  /**
   * @param $label
   * @return array
   */
  public function standard_options($label)
  {
    return array('required' => FALSE, 'label' => $label, 'translation_domain' => 'bootsy_user');
  }
}