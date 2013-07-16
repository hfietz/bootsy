<?php

namespace Econemon\Bootsy\DatabaseBundle\Service;

use Econemon\Bootsy\ApplicationBundle\Exception\DefensiveCodeException;

abstract class BaseObjectManager implements DatabaseServiceAware, ObjectManagerInterface
{
  /**
   * @var DatabaseService
   */
  protected $databaseService;

  /**
   * @var string
   */
  protected $className;

  /**
   * @var GeneralClassManager
   */
  protected $classManager = NULL;

  /**
   * @param string $className
   */
  public function __construct($className)
  {
    if (!class_exists($className)) {
      throw DefensiveCodeException::fromBaseMessage("Class '%class%' doesn't exist", array('class' => $className));
    }

    $this->className = $className;
  }

  /**
   * @param \Econemon\Bootsy\DatabaseBundle\Service\DatabaseService $databaseService
   */
  function setDatabaseService(DatabaseService $databaseService = NULL)
  {
    $this->databaseService = $databaseService;
  }

  /**
   * @return \Econemon\Bootsy\DatabaseBundle\Service\GeneralClassManager
   */
  protected function getClassManager()
  {
    if (NULL === $this->classManager) {
      $this->classManager = $this->createClassManager($this->className);
    }

    return $this->classManager;
  }

  protected function createClassManager($className)
  {
    return new GeneralClassManager($className, $this->getDoctrine());
  }

  /**
   * @return \Symfony\Bridge\Doctrine\RegistryInterface
   * @throws \Econemon\Bootsy\ApplicationBundle\Exception\DefensiveCodeException
   */
  protected function getDoctrine()
  {
    $doctrine = $this->databaseService->getDoctrine();
    if (NULL === $doctrine) {
      throw new DefensiveCodeException("Doctrine not found");
    }

    return $doctrine;
  }

  /**
   * @inheritdoc
   */
  public function findOneBy(array $criteria, array $orderBy = NULL)
  {
    return $this->getClassManager()->findOneBy($criteria, $orderBy);
  }

  /**
   * @inheritdoc
   */
  public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
  {
    return $this->getClassManager()->findBy($criteria, $orderBy, $limit, $offset);
  }

  /**
   * @inheritdoc
   */
  public function newInstance()
  {
    return $this->getClassManager()->newInstance();
  }

  /**
   * @inheritdoc
   */
  public function save($object)
  {
    $this->getClassManager()->save($object);
  }

  /**
   * @inheritdoc
   */
  public function getClassName()
  {
    return $this->getClassManager()->getClassName();
  }

  public function startTransaction()
  {
    $this->databaseService->startTransaction();
  }

  public function commitTransaction()
  {
    $this->databaseService->commitTransaction();
  }

  public function rollbackTransaction()
  {
    $this->databaseService->rollbackTransaction();
  }
}