<?php

namespace Drupal\prometheus_check_item;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\node\NodeInterface;
use GuzzleHttp\ClientInterface;

/**
 * Dispatches the checks to the cloud.
 */
class TriggerCheck {

  /**
   * Status: To Process.
   */
  public const TO_PROCESS_STATUS = 'to_process';

  /**
   * Status: Checking.
   */
  public const CHECKING_STATUS = 'checking';

  /**
   * The fallback cloud run url.
   */
  public const DEFAULT_CURL_CLOUD_URL = 'https://prometheuscurl-k7x262eijq-od.a.run.app';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected ClientInterface $client;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $logger;

  /**
   * The check node.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected NodeInterface $check;

  /**
   * The cloud url to use.
   *
   * @var string
   */
  protected string $cloudUrl;

  /**
   * The data to be sent to cloud.
   *
   * @var array
   */
  protected array $checkData;

  /**
   * Constructs a TriggerCheck object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ClientInterface $client, LoggerChannelFactoryInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->client = $client;
    $this->logger = $logger;
  }

  /**
   * Prepares and dispatches the checks.
   *
   * @param $intervalType
   *   The interval type used.
   *
   * @return void
   */
  public function trigger($intervalType): void {
    $properties = [
      'type' => 'check',
      'field_check_interval' => $intervalType,
      'field_check_status' => 'active',
    ];

    /** @var \Drupal\node\NodeInterface $checks */
    $checks = $this->entityTypeManager->getStorage('node')->loadByProperties($properties);
    if (empty($checks)) {
      // Log.
      return;
    }

    foreach ($checks as $check) {
      $this->check = $check;
      $checkItemId = $this->createCheckItem();
      $this->checkData = $this->buildCheckData($checkItemId);
      $this->dispatch();
    }
  }

  /**
   * Creates the check item node.
   *
   * @return int
   */
  protected function createCheckItem(): int {
    $checkItem = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'check_item',
      'field_dom_content_loaded' => 0,
      'field_page_load' => 0,
      'field_check_ref' => ['target_id' => $this->check->id()],
      'field_status' => self::CHECKING_STATUS,
    ]);

    $checkItem->save();
    return $checkItem->id();
  }

  /**
   * Builds the POST body data which will be sent.
   *
   * @param $checkItemId
   *   The check item nid.
   *
   * @return array
   *   The data
   */
  protected function buildCheckData($checkItemId): array {
    return [
      'url' => $this->check->get('field_check_resource')->value,
      'type' => $this->check->get('field_type')->value,
      'check_item_id' => (string) $checkItemId,
       'cloud_url' => $this->getCloudUrl(),
//      'cloud_url' => 'https://c348bb49-5218-4de9-ba34-732f9a0f2106.mock.pstmn.io',
      'from_host' => \Drupal::request()->getSchemeAndHttpHost() . '/api/check_item/update',
    ];
  }

  /**
   * Dispatches the check to Cloud run.
   *
   * @return void
   */
  protected function dispatch(): void {
    $this->cloudUrl = $this->checkData['cloud_url'];
    // $this->cloudUrl = 'http://localhost:8080';
    try {
      $response = $this->client->request(
        'POST',
        $this->cloudUrl,
        [
          'json' => $this->checkData,
        ]
      );
    }
    catch (\Exception $exception) {
      $msg = $exception->getMessage();
      \Drupal::logger('test')->error($msg);
    }

  }

  /**
   * Determines the Cloud run url.
   *
   * @return string
   *   The url.
   */
  protected function getCloudUrl(): string {
    if ($this->checkData['type'] = 'url') {
      $fieldName = 'field_curl_cloud_url';

      /** @var \Drupal\taxonomy\Entity\Term $regionTerm */
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

    return 'Not supported now';
  }

}
