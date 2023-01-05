<?php

namespace Drupal\prometheus_check_item\EventSubscriber;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\prometheus_check_item\Event\CheckItemAnalyzeEvent;
use Drupal\prometheus_check_item\TriggerCheck;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Class UserLoginSubscriber.
 *
 * @package Drupal\custom_events\EventSubscriber
 */
class CheckItemAnalyzeSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private MessengerInterface $messenger;

  /**
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private DateFormatterInterface $date_formatter;

  /**
   * LoginEventSubscriber constructor.
   *
   * @param  \Drupal\Core\Messenger\MessengerInterface  $messenger
   */
  public function __construct(MessengerInterface $messenger, DateFormatterInterface $date_formatter) {
    $this->messenger = $messenger;
    $this->date_formatter = $date_formatter;
  }

  /**
   * @return array
   */
  public static function getSubscribedEvents() {
    //    $events[CheckItemAnalyzeEvent::EVENT_NAME][] = ['onSave', 34];
    //    return $events;


    return [
      CheckItemAnalyzeEvent::EVENT_NAME => 'onSave',
    ];
  }

  /**
   * Subscribe to the node save event dispatched.
   *
   * @param  \Drupal\prometheus_check_item\Event\CheckItemAnalyzeEvent  $event
   *   Dat event object yo.
   */
  public function onSave(CheckItemAnalyzeEvent $event) {
    \Drupal::messenger()->addError('Test');
    if ($event->node->bundle() !== 'check_item'
      || $event->node->get('field_status')->isEmpty()
      || $event->node->get('field_status')->value !== TriggerCheck::TO_PROCESS_STATUS
    ) {
      return;
    }


    // Evaluate status code.
    // Evaluate SSL validation
    // Trigger notifications?
  }

}