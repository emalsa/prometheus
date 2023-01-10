<?php

namespace Drupal\jwt_auth_login_issuer\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\jwt\Authentication\Event\JwtAuthEvents;
use Drupal\jwt\Authentication\Event\JwtAuthGenerateEvent;
use Drupal\jwt\Authentication\Provider\JwtAuth;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Component\Serialization\Json;

/**
 * Add JWT on login.
 */
class JwtAuthLoginIssuerSubscriber implements EventSubscriberInterface {

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;
  /**
   * CurrentRouteMatch definition.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * JWT auth service.
   *
   * @var \Drupal\jwt\Authentication\Provider\JwtAuth
   */
  protected $jwtAuth;

  /**
   * Config storage.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Constructor function.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   Current route match.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current logged in user.
   * @param \Drupal\jwt\Authentication\Provider\JwtAuth $jwt_auth
   *   JWT Auth service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Config Storage.
   */
  public function __construct(CurrentRouteMatch $route_match, AccountProxyInterface $current_user, JwtAuth $jwt_auth, ConfigFactoryInterface $config) {
    $this->currentRouteMatch = $route_match;
    $this->currentUser = $current_user;
    $this->jwtAuth = $jwt_auth;
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[KernelEvents::RESPONSE][] = ['OnHttpLoginResponse'];
    $events[JwtAuthEvents::GENERATE][] = ['setStandardClaims', 98];
    $events[JwtAuthEvents::GENERATE][] = ['setDrupalClaims', 99];
    return $events;
  }

  /**
   * On REST login append a bearer token to the response.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   Response event object.
   */
  public function onHttpLoginResponse(FilterResponseEvent $event) {
    if ($event->getResponse()->getStatusCode() === 200 && !$this->currentUser->isAnonymous()) {
      if ($this->currentRouteMatch->getRouteName() !== 'user.login.http') {
        return;
      }
      $response = $event->getResponse();
      if ($body = Json::decode($response->getContent())) {
        if ($token = $this->jwtAuth->generateToken()) {
          $body['jwt_token'] = $token;
          $content = Json::encode($body);
          if ($content) {
            $event->getResponse()->setContent($content);
            $event->setResponse($response);
          }
        }
      }
    }
  }

  /**
   * Sets the standard claims set for a JWT.
   *
   * @param \Drupal\jwt\Authentication\Event\JwtAuthGenerateEvent $event
   *   The event.
   */
  public function setStandardClaims(JwtAuthGenerateEvent $event) {
    $expire = $this->config->get('jwt_auth_login_issuer.config')->get('expiry');
    $event->addClaim('iat', time());
    $event->addClaim('exp', strtotime('+' . $expire . ' minute'));
  }

  /**
   * Sets claims for a Drupal consumer on the JWT.
   *
   * @param \Drupal\jwt\Authentication\Event\JwtAuthGenerateEvent $event
   *   The event.
   */
  public function setDrupalClaims(JwtAuthGenerateEvent $event) {
    $event->addClaim(
      ['drupal', 'uid'],
      $this->currentUser->id()
    );
  }

}
