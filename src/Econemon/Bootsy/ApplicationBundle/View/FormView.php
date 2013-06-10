<?php

namespace Econemon\Bootsy\ApplicationBundle\View;

use Symfony\Component\Form\FormInterface;

class FormView extends MainView
{
  /**
   * @var FormInterface
   */
  protected $form;

  public function __construct(FormInterface $form)
  {
    $this->form = $form->createView();
  }

  /**
   * @return \Symfony\Component\Form\FormView
   */
  public function getForm()
  {
    return $this->form;
  }
}