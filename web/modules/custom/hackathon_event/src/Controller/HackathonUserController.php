<?php

namespace Drupal\hackathon_event\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Unicode;
use Drupal\user\Entity\User;

class HackathonUserController extends ControllerBase {

  /**
   * Returns response for the autocompletion.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object containing the search string.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions.
   */

  public function autocomplete(request $request) {
    $matches = array();
    $string = $request->query->get('q');
    if ($string) {
        $matches = array();

        // $ids = \Drupal::entityQuery('user')
        // ->condition('status', 1)
        // ->condition('roles', ['leader', 'developer'], 'IN')
        // ->execute();
        // $users = User::loadMultiple($ids);
        $users = User::loadMultiple([1]);


      foreach ($users as $user_data) {
        $matches[] = ['value' => $user_data->getUsername() . " (".$user_data->id().")", 'label' => $user_data->getUsername()];
      }
    }
    return new JsonResponse($matches);
  }
}