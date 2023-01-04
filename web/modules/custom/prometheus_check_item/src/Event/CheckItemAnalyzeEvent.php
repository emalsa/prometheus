<?php

namespace Drupal\prometheus_check_item\Event;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user logs in.
 */
class CheckItemAnalyzeEvent extends Event {

  const EVENT_NAME = 'check_item_analyze_event';

  /**
   * @var \Drupal\Core\Entity\EntityInterface
   */
  public EntityInterface $node;

  /**
   * Constructs the object.
   *
   * @param  \Drupal\node\NodeInterface  $node
   *   The account of the user logged in.
   */
  public function __construct(EntityInterface $node) {
    $this->node = $node;
  }

}