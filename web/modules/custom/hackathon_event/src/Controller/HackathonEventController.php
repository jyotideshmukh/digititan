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
    \Drupal::service('exela_csr.visitor_counter')->recordView();

   return $response = new Response( 'Counter Incremented', Response::HTTP_OK, array('content-type' => 'text/html') ); 
  }

  public function show($eventid){

  }

   public function list(){

     $database = \Drupal::database();
 $query = $database->select('hackathon_event', 'e');
 //$query->join('users','u')
// Add extra detail to this query object: a condition, fields and a range.

$query->fields('e', ['name', 'description', 'FROM_UNIXTIME(event_start_date) as e_startdate', 'FROM_UNIXTIME(event_end_date)', 'FROM_UNIXTIME(reg_start_date)', 'FROM_UNIXTIME(reg_end_date)']);
$query->range(0, 50);
$results = $query->execute();
foreach($result as $result){
  echo  $row->name, $row->description, $row->e_startdate;
}

  }

}
