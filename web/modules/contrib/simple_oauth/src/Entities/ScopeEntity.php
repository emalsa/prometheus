<?php

namespace Drupal\simple_oauth\Entities;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\user\RoleInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ScopeEntity implements ScopeEntityNameInterface {

  use EntityTrait, RefinableCacheableDependencyTrait;

  /**
   * The name of this scope.
   *
   * @var string
   */
  protected $name;

  /**
   * The role associated to the scope.
   *
   * @var \Drupal\user\RoleInterface
   */
  protected $role;

  /**
   * Construct a ScopeEntity instance.
   *
   * @param \Drupal\user\RoleInterface $role
   *   The role associated to the scope.
   */
  public function __construct(RoleInterface $role) {
    $this->role = $role;
    $this->setIdentifier($role->id());
    $this->name = $role->label();
    $this->addCacheableDependency($role);
  }

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return $this->getIdentifier();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    // Roles have no description.
    return NULL;
  }

}
