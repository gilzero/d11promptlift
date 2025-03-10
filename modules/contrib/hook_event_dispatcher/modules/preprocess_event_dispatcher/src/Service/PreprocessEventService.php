<?php

namespace Drupal\preprocess_event_dispatcher\Service;

use Drupal\preprocess_event_dispatcher\Event\PreprocessEntityEventInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class PreprocessEventService.
 */
final class PreprocessEventService implements PreprocessEventServiceInterface {

  /**
   * PreprocessEventService constructor.
   *
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event dispatcher.
   * @param PreprocessEventFactoryMapper $mapper
   *   Factory mapper.
   */
  public function __construct(private readonly EventDispatcherInterface $dispatcher, private readonly PreprocessEventFactoryMapper $mapper) {
  }

  /**
   * {@inheritdoc}
   */
  public function createAndDispatchKnownEvents(string $hook, array &$variables): void {
    $factory = $this->mapper->getFactory($hook);
    if ($factory === NULL) {
      return;
    }

    $event = $factory->createEvent($variables);
    $this->dispatcher->dispatch($event, $event::name());

    if ($event instanceof PreprocessEntityEventInterface) {
      $this->dispatchEntitySpecificEvents($event);
    }
  }

  /**
   * Dispatch the entity events.
   *
   * @param \Drupal\preprocess_event_dispatcher\Event\PreprocessEntityEventInterface $event
   *   The event to dispatch.
   */
  private function dispatchEntitySpecificEvents(PreprocessEntityEventInterface $event): void {
    $variables = $event->getVariables();

    $withBundle = $event::name($variables->getEntityBundle());
    $this->dispatcher->dispatch($event, $withBundle);

    $withViewMode = $event::name($variables->getEntityBundle(), $variables->getViewMode());
    $this->dispatcher->dispatch($event, $withViewMode);
  }

}
