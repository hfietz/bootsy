<?php

namespace Econemon\Bootsy\UserBundle\Form\Type;

use Econemon\Bootsy\ApplicationBundle\Form\BaseForm;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class NewUserFormType extends BaseForm
{
  const FORM_NAME = 'econemon_bootsy_user_new_form';

  const DATA_CLASS = 'Econemon\Bootsy\UserBundle\Form\Model\NewUserData';

  const TRANSLATION_DOMAIN = 'bootsy_user';

  const DEFAULT_ROLE_NAME = 'ROLE_USER';

  /**
   * @var TranslatorInterface
   */
  protected $translator;

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $this->addSelect($builder, 'role', 'registration.form.role', $this->getRoleOptions(), self::TRANSLATION_DOMAIN);

    $builder->add('email', 'repeated', array(
      'type' => 'email',
      'options' => array('translation_domain' => self::TRANSLATION_DOMAIN),
      'first_options' => array('label' => 'registration.form.email'),
      'second_options' => array('label' => 'registration.form.emailConfirm'),
      'invalid_message' => 'registration.form.messages.emailMismatch',
    ));
  }

  /**
   * Returns the name of this type.
   *
   * @return string The name of this type
   */
  public function getName()
  {
    return self::FORM_NAME;
  }

  public function setDefaultOptions(OptionsResolverInterface $resolver)
  {
    parent::setDefaultOptions($resolver);

    $resolver->setDefaults(array(
      'data_class' => self::DATA_CLASS,
    ));
  }

  public function getRoleOptions()
  {
    $options = array();

    foreach (array('ROLE_OBSERVER', self::DEFAULT_ROLE_NAME, 'ROLE_ADMIN') as $role) {
      $roleDescription = $this->translator->trans('roles.' . $role, array(), self::TRANSLATION_DOMAIN);
      $options[$role] = $roleDescription;
    }

    return $options;
  }

  public function getDefaultRoleOption()
  {
    return self::DEFAULT_ROLE_NAME;
  }

  /**
   * @param \Symfony\Component\Translation\TranslatorInterface $translator
   */
  public function setTranslator($translator)
  {
    $this->translator = $translator;
  }
}