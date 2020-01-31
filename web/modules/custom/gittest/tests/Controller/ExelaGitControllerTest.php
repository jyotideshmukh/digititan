<?php

namespace Drupal\gittest\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the gittest module.
 */
class ExelaGitControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "gittest ExelaGitController's controller functionality",
      'description' => 'Test Unit for module gittest and controller ExelaGitController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests gittest functionality.
   */
  public function testExelaGitController() {
    // Check that the basic functions of module gittest.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
