<?php

namespace Drupal\prometheus_check_item;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;
use GuzzleHttp\ClientInterface;

/**
 * Service description.
 */
class TriggerCheck {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  protected NodeInterface $check;

  protected array $checkData;

  protected string $cloudUrl;

  const DEFAULT_CURL_CLOUD_URL = 'https://prometheuscurl-k7x262eijq-od.a.run.app';

  /**
   * Constructs a TriggerCheck object.
   *
   * @param  \Drupal\Core\Entity\EntityTypeManagerInterface  $entity_type_manager
   *   The entity type manager.
   * @param  \GuzzleHttp\ClientInterface  $client
   *   The HTTP client.
   * @param  \Drupal\Core\Logger\LoggerChannelFactoryInterface  $logger
   *   The logger channel factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ClientInterface $client, LoggerChannelFactoryInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->client = $client;
    $this->logger = $logger;
  }

  /**
   * Method description.
   */
  public function trigger($interval) {
    $properties = $this->getCheckProperties($interval);
    $checks = $this->entityTypeManager->getStorage('node')->loadByProperties($properties);
    if (empty($checks)) {
      // Log.
      return;
    }

    foreach ($checks as $check) {
      $checkData = [];
      $this->checkData = [];
      $this->check = $check;
      $checkItemId = $this->createCheckItem();
      $this->checkData = $this->getCheckData($checkItemId);
      $this->dispatch();
    }
  }

  protected function dispatch() {
    $this->cloudUrl = $this->checkData['cloud_url'];
//    $this->cloudUrl = 'http://localhost:8080';
    $response = $this->client->request(
      'POST',
      $this->cloudUrl,
      [
        'form_params' => $this->checkData,
      ]
    );
    $a = 1;
  }


  protected function getCheckProperties($interval) {
    return [
      'type' => 'check',
      'field_check_interval' => $interval,
      'field_check_status' => 'active',
    ];
  }


  protected function getCheckData($checkItemId) {
    return [
      'url' => $this->check->get('field_check_resource')->value,
      'type' => $this->check->get('field_type')->value,
      'check_item_id' => $checkItemId,
      'cloud_url' => $this->getCloudUrl(),
    ];
  }

  protected function getCloudUrl(): string {
    if ($this->checkData['type'] = 'url') {
      $fieldName = 'field_curl_cloud_url';
    }

    $regionTerm = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => 'regions',
      'field_region_status' => 'active',
    ]);

    if (empty($regionTerm)) {
      // Log!
      return self::DEFAULT_CURL_CLOUD_URL;
    }
    return $regionTerm->get($fieldName)->value;
  }

  protected function createCheckItem() {
    $checkItem = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'check_item',
      'field_dom_content_loaded' => 0,
      'field_page_load' => 0,
      'field_check_ref' => ['target_id' => $this->check->id()],
      'field_status' => 'active',
    ]);

    $checkItem->save();
    $a = 1;
    return $checkItem->id();
  }


}
