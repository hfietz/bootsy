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

    $builder->add('name', 'text', array('required' => FALSE, 'label' => 'form.user.name', 'translation_domain' => 'bootsy_user'));
    $builder->add('phone', 'text', array('required' => FALSE, 'label' => 'form.user.phone', 'translation_domain' => 'bootsy_user'));
  }

  public function getName()
  {
    return self::FORM_NAME;
  }
}