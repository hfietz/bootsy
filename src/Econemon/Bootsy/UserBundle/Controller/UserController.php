<?php

namespace Econemon\Bootsy\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class UserController
{
  /**
   * @var HttpKernel
   */
  protected $httpKernel;

  public function editProfileAction(Request $request)
  {
    return $this->httpKernel->forward('FOSUserBundle:Profile:edit');
  }

  /**
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   */
  public function setHttpKernel($httpKernel)
  {
    $this->httpKernel = $httpKernel;
  }
}