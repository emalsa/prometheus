<?php

namespace Drupal\prometheus_check_item\Plugin\rest\resource;

use Drupal\Core\Config\Config;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Utility\Token;
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

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param  array  $config
   *   A configuration array which contains the information about the plugin
   *   instance.
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
  public function __construct(
    array $config,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($config, $plugin_id, $plugin_definition,
      $serializer_formats, $logger);

    $this->loggedUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container,
    array $config,
    $plugin_id,
    $plugin_definition) {
    return new static(
      $config,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('sample_rest_resource'),
      $container->get('current_user')
    );
  }

  public function get() {
    $result=['ss'=>'test','wow'=>['nein','nesin']];

    $response = new ResourceResponse($result);
    $response->addCacheableDependency($result);
    return $response;
  }

  /**
   * Responds to GET request.
   * Returns a list of taxonomy terms.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   * Throws exception expected.
   */
  public function post(Request $request) {
//    return;
//    // Implementing our custom REST Resource here.
//    // Use currently logged user after passing authentication and validating the access of term list.
//    if (!$this->loggedUser->hasPermission('access content')) {
//      throw new AccessDeniedHttpException();
//    }
//    $vid = 'vb';
//    $terms = \Drupal::entityTypeManager()
//      ->getStorage('taxonomy_term')
//      ->loadTree($vid);
//    foreach ($terms as $term) {
//      $term_result[] = [
//        'id' => $term->tid,
//        'name' => $term->name,
//      ];
//    }

    json_decode($request->getContent());
    $result=['ss'=>'test','wow'=>['nein','nesin']];

    $response = new ResourceResponse($result);
    $response->addCacheableDependency($result);
    return $response;
  }

}
