<?php

namespace Econemon\Bootsy\ApplicationBundle\DependencyInjection;

class ServiceInterfaceDescription
{
  protected $interfaceName;

  protected $registrationMethod;

  protected $serviceId;

  public function __construct($serviceId, $interfaceName, $registrationMethod)
  {
    $this->serviceId = $serviceId;
    $this->interfaceName = $interfaceName;
    $this->registrationMethod = $registrationMethod;
  }

  /**
   * @return string
   */
  public function getInterfaceName()
  {
    return $this->interfaceName;
  }

  /**
   * @return string
   */
  public function getRegistrationMethod()
  {
    return $this->registrationMethod;
  }

  /**
   * @return string
   */
  public function getServiceId()
  {
    return $this->serviceId;
  }
}