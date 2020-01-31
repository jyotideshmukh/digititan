<?php

namespace Drupal\hackathon_event\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class HackathonEventController.
 */
class HackathonEventController extends ControllerBase implements ContainerInjectionInterface{

  protected $request;

  /**
   * Constructs a WebformAddonsController object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
    );
  }

  /**
   * Recordvisit.
   *
   * @return string
   *   Return Hello string.
   */
  public function recordVisit() {
    \Drupal::service('hackathon_event.visitors')->recordView();

   return $response = new Response( 'Counter Incremented', Response::HTTP_OK, array('content-type' => 'text/html') ); 
  }

}
