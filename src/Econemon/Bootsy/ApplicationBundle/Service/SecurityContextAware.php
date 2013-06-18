<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

use Symfony\Component\Security\Core\SecurityContextInterface;

interface SecurityContextAware
{
  const EXPECTED_SERVICE_ID = 'security.context';

  const CLIENT_IFACE = 'Econemon\Bootsy\ApplicationBundle\Service\SecurityContextAware';

  const SETTER_NAME = 'setSecurityContext';

  public function setSecurityContext(SecurityContextInterface $securityContext);
}