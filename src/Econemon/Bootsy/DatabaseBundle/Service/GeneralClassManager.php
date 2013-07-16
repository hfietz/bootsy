<?php

namespace Econemon\Bootsy\DatabaseBundle\Service;

use Doctrine\DBAL\LockMode;
use Econemon\Bootsy\ApplicationBundle\Exception\DefensiveCodeException;
use Symfony\Bridge\Doctrine\RegistryInterface;

class GeneralClassManager implements ObjectManagerInterface
{
  /**
   * @var \Doctrine\ORM\EntityManager|null
   */
  protected $em;

  /**
   * @var \Doctrine\ORM\EntityRepository
   */
  protected $repo;

  /**
   * @var string
   */
  protected $className;

  /**
   * @param string $className
   * @param RegistryInterface $doctrine
   */
  public function __construct($className, RegistryInterface $doctrine)
  {
    $params = array('class' => $className);

    if (!class_exists($className)) {
      throw DefensiveCodeException::fromBaseMessage("Class '%class% does not exist", $params);
    }

    $this->em = $doctrine->getEntityManagerForClass($className);

    if (NULL === $this->em) {
      throw DefensiveCodeException::fromBaseMessage("No entity manager found for class '%class%'", $params);
    }

    $this->repo = $this->em->getRepository($className);

    $this->className = $className;
  }

  /**
   * @param array $criteria
   * @param array $orderBy
   * @return object|null
   */
  public function findOneBy(array $criteria, array $orderBy = NULL)
  {
    return $this->repo->findOneBy($criteria, $orderBy);
  }

  /**
   * @param array $criteria
   * @param array $orderBy
   * @param null $limit
   * @param null $offset
   * @return array
   */
  public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
  {
    return $this->repo->findBy($criteria, $orderBy, $limit, $offset);
  }

  /**
   * @return object
   */
  public function newInstance()
  {
    return new $this->className();
  }

  /**
   * @param object $object
   * @throws \Econemon\Bootsy\ApplicationBundle\Exception\DefensiveCodeException
   */
  public function save($object)
  {
    if (!is_a($object, $this->className)) {
      throw DefensiveCodeException::forUnexpectedTypeOf($object, $this->className);
    }

    $this->em->persist($object);
    $this->em->flush();
  }

  /**
   * @return string
   */
  public function getClassName()
  {
    return $this->className;
  }

  /**
   * Finds an entity by its primary key / identifier.
   *
   * @param mixed $id The identifier.
   * @param integer $lockMode
   * @param integer $lockVersion
   *
   * @return object The entity.
   */
  public function find($id, $lockMode = LockMode::NONE, $lockVersion = NULL)
  {
    return $this->repo->find($id, $lockMode, $lockVersion);
  }

  public function findAll()
  {
    return $this->repo->findAll();
  }
}