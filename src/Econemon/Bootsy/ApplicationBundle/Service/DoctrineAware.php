<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;

interface DoctrineAware
{
  const DOCTRINE_SERVICE_ID = 'doctrine';

  const DOCTRINE_CLIENT_IFACE = __CLASS__;

  const SET_DOCTRINE = 'setDoctrine';

  public function setDoctrine(RegistryInterface $doctrine);
}