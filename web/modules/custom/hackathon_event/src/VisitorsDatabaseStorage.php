<?php

namespace Drupal\hackathon_event;

use Drupal\Core\Database\Connection;

/**
 * Provides the default database storage backend for statistics.
 */
class VisitorsDatabaseStorage {

  /**
  * The database connection used.
  *
  * @var \Drupal\Core\Database\Connection
  */
  protected $connection;

  /**
   * Constructs the statistics storage.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection for the node view storage.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function recordView() {
    try {
        $counter = \Drupal::config('hackathon_event.visitors')->get('counter');
        if (!empty($counter)) {
            $counter = $counter + 1;
        } else {
          $counter = 1;
        }
        $config_factory = \Drupal::configFactory();
        $config = $config_factory->getEditable('hackathon_event.visitors');
        $config->set('counter', $counter);
        $config->save();
      
    } catch (Exception $e) {
      \Drupal::logger('hackathon_event')->error($e->getMessage());
    }
    
  }
}
