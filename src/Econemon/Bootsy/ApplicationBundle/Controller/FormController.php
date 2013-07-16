<?php

namespace Econemon\Bootsy\ApplicationBundle\Controller;

use Econemon\Bootsy\ApplicationBundle\Service\FormFactoryAware;

use Symfony\Component\Form\FormFactoryInterface;

abstract class FormController extends BaseController implements FormFactoryAware
{
  /**
   * @var FormFactoryInterface
   */
  protected $formFactory;

  /**
   * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
   */
  public function setFormFactory($formFactory)
  {
    $this->formFactory = $formFactory;
  }
}