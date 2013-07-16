<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

interface FormFactoryAware
{
  const FORM_FACTORY_SERVICE_ID = 'form.factory';

  const FORM_FACTORY_CLIENT_IFACE = __CLASS__;

  const FORM_FACTORY_SETTER_NAME = 'setFormFactory';

  /**
   * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
   */
  public function setFormFactory($formFactory);
}