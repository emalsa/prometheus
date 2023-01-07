<?php

namespace Drupal\prometheus_check_item;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;

/**
 * Service description.
 */
class EvaluateChecks {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $logger;

  /**
   * The node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected NodeInterface $checkItem;

  /**
   * The received data.
   *
   * @var array
   */
  protected array $responseData = [];

  /**
   * Constructs an EvaluateChecks object.
   *
   * @param  \Drupal\Core\Entity\EntityTypeManagerInterface  $entity_type_manager
   *   The entity type manager.
   * @param  \Drupal\Core\Logger\LoggerChannelFactoryInterface  $logger
   *   The logger channel factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
  }

  /**
   * Evaluates the results.
   *
   * @return void
   */
  public function evaluate() {
    $checkItems = $this->getCheckItemNodes();
    if (!$checkItems) {
      return;
    }

    foreach ($checkItems as $this->checkItem) {
      $this->checkItem->set('field_status', TriggerCheck::TO_PROCESS_STATUS);
      $this->checkItem->save();
      $this->evaluateResult();
    }
  }

  /**
   * Gets the nodes to check
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|null
   *   The nodes array
   */
  protected function getCheckItemNodes() {
    $query = \Drupal::entityQuery('node');
    $query->accessCheck(FALSE)
      ->condition('type', 'check_item')
      ->condition('field_status', TriggerCheck::CHECKED_STATUS)
      ->sort('changed', 'ASC')
      ->range(0, 25);

    $nids = $query->execute();
    if (empty($nids)) {
      $this->logger->get('evaluate')->error('No Items no evaluate');
      return NULL;
    }

    return $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
  }

  /**
   * Evaluates the results.
   *
   * @return void
   */
  protected function evaluateResult() {
    $this->responseData = [];
    if ($this->checkItem->get('field_full_response')->isEmpty()) {
      $this->logger->get('evaluate')->error('Empty response');
      return;
    }

    $response = json_decode($this->checkItem->get('field_full_response')->value, TRUE);
    if (empty($response['response'])) {
      $this->logger->get('evaluate')->error('Could not json_decode response');
      return;
    }

    $responseData = base64_decode($response['response']);
    $responseData = preg_split("/\\r\\n|\\r|\\n/", $responseData);
    if (empty($responseData)) {
      $this->logger->get('evaluate')->error('Could not split response into array');

      return;
    }

    $this->responseData = $responseData;
    $this->setTimes();
  }

  /**
   * Sets the times.
   *
   * @return void
   */
  protected function setTimes(): void {
    $searchFields = [
      'field_time_appconnect' => 'time_appconnect',
      'field_time_connect' => 'time_connect',
      'field_time_namelookup' => 'time_namelookup',
      'field_time_pretransfer' => 'time_pretransfer',
      'field_time_redirect' => 'time_redirect',
      'field_time_starttransfer' => 'time_starttransfer',
      'field_time_total' => 'time_total',
    ];

    // Array reverse because the search text are below.
    foreach (array_reverse($this->responseData) as $item) {
      foreach ($searchFields as $searchField => $searchString) {
        if (str_starts_with($item, "$searchString:")) {
          $val = str_replace("$searchString: ", '', $item);
          $val = str_replace('s', '', $val);
          $this->checkItem->set($searchField, $val);
        }
      }
    }
    $this->checkItem->save();
  }

}
