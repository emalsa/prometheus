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


  public function evaluate() {
    $checkItems = $this->getCheckItemNodes();
    if (!$checkItems) {
      return;
    }

    foreach ($checkItems as $this->checkItem) {
      $this->evaluateResult();
    }
  }

  protected function getCheckItemNodes() {
    $query = \Drupal::entityQuery('node');
    $query->accessCheck(FALSE)
      ->condition('type', 'check_item')
      ->condition('field_status', TriggerCheck::TO_PROCESS_STATUS)
      ->sort('changed', 'ASC')
      ->range(0, 5);

    $nids = $query->execute();
    if (empty($nids)) {
      // Log.
      return NULL;
    }

    return $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
  }

  protected function evaluateResult() {
    $this->responseData = [];
    if ($this->checkItem->get('field_full_response')->isEmpty()) {
      // Log.
      return;
    }

    $response = json_decode($this->checkItem->get('field_full_response')->value, TRUE);
    if (empty($response['response'])) {
      // Log.
      return;
    }

    $responseData = base64_decode($response['response']);
    $responseData = preg_split("/\\r\\n|\\r|\\n/", $responseData);
    if (empty($responseData)) {
      // Log!
      return;
    }

    $this->responseData = $responseData;
    $this->setTimes();
  }


  protected function setTimes() {
    $searchFields = [
      'field_time_appconnect' => 'time_appconnect',
      'field_time_connect' => 'time_connect',
      'field_time_namelookup' => 'time_namelookup',
      'field_time_pretransfer' => 'time_pretransfer',
      'field_time_redirect' => 'time_redirect',
      'field_time_starttransfer' => 'time_starttransfer',
      'field_time_total' => 'time_total',
    ];

    foreach ($this->responseData as $item) {
      foreach ($searchFields as $searchField => $searchString) {
        if (str_starts_with($item, "$searchString:")) {
          $val = str_replace("$searchString: ", '', $item);
          $val = str_replace('s', '', $val);
          $this->checkItem->set($searchField, $val);
        }
      }
      $this->checkItem->save();
    }
  }

}
