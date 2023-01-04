<?php

namespace Drupal\prometheus_check_item\Plugin\rest\resource;

use Drupal\Core\Config\Config;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Utility\Token;
use Drupal\node\NodeInterface;
use Drupal\prometheus_check_item\TriggerCheck;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource as endpoint to update check item with data.
 *
 * @RestResource(
 *   id = "check_item",
 *   label = @Translation("Check item"),
 *   uri_paths = {
 *     "canonical" = "/api/check_item/test",
 *     "create" = "/api/check_item/update"
 *   }
 * )
 */
class CheckItemResource extends ResourceBase {


  /**
   * A current user instance which is logged in the session.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $loggedUser;

  protected array $jsonData;

  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param  array  $config
   *   A configuration array which contains the information about the plugin instance.
   * @param  string  $plugin_id
   *   The module_id for the plugin instance.
   * @param  mixed  $plugin_definition
   *   The plugin implementation definition.
   * @param  array  $serializer_formats
   *   The available serialization formats.
   * @param  \Psr\Log\LoggerInterface  $logger
   *   A logger instance.
   * @param  \Drupal\Core\Session\AccountProxyInterface  $current_user
   *   A currently logged user instance.
   */
  public function __construct(array $config,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($config, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->loggedUser = $current_user;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $config, $plugin_id, $plugin_definition) {
    return new static(
      $config,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('sample_rest_resource'),
      $container->get('current_user'),
      $container->get('entity_type.manager')
    );
  }

  //  public function get() {
  //    $result = ['ss' => 'test', 'wow' => ['nein', 'nesin']];
  //
  //    $response = new ResourceResponse($result);
  //    $response->addCacheableDependency($result);
  //    return $response;
  //  }

  /**
   * Responds to POST request.
   */
  public function post(Request $request) {
    try {
      $this->jsonData = json_decode($request->getContent(), TRUE);
      if ($this->jsonData['success']) {
        $this->onSuccess();
      }
      \Drupal::logger('check_item_resource')->notice($request->getContent());
      $result = ['ss' => 'test', 'wow' => ['nein', 'ujjjj']];

      $response = new ResourceResponse($result);
      $response->addCacheableDependency($result);
      return $response;
    }
    catch (\Exception $exception) {
      // Log!
    }


  }

  protected function onSuccess() {
    // Load Check item by id
    $node = $this->getCheckItemNode();
    if (!$node) {
      // Log!
      return;
    }

    $node->set('field_full_response', json_encode($this->jsonData));
    $node->set('field_status', TriggerCheck::TO_PROCESS_STATUS);
    $node->save();
  }

  protected function getCheckItemNode(): NodeInterface|null {
    $node = $this->entityTypeManager->getStorage('node')->load($this->jsonData['check_item_id']);
    if (!$node instanceof NodeInterface || $node->bundle() !== 'check_item') {
      // Log!
      return NULL;
    }

    if ($node->get('field_status')->isEmpty() || $node->get('field_status')->value !== TriggerCheck::CHECKING_STATUS) {
      // Log!
      return NULL;
    }

    return $node;
  }

}
