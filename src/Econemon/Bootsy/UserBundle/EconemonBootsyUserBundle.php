<?php
namespace Econemon\Bootsy\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class EconemonBootsyUserBundle extends Bundle
{
  public function getParent()
  {
    return 'FOSUserBundle';
  }

}