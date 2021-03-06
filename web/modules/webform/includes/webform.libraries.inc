<?php

/**
 * @file
 * Webform libraries.
 */

/**
 * Implements hook_library_info_alter().
 */
function webform_library_info_alter(&$libraries, $extension) {
  // Only alter modules that declare webform libraries.
  // @see hook_webform_libraries_info()
  $webform_libraries_modules = \Drupal::moduleHandler()->getImplementations('webform_libraries_info');
  $webform_libraries_modules[] = 'webform';
  if (!in_array($extension, $webform_libraries_modules)) {
    return;
  }

  // If chosen_lib.module is installed, then update the dependency.
  if (\Drupal::moduleHandler()->moduleExists('chosen_lib')) {
    if (isset($libraries['webform.element.chosen'])) {
      $dependencies =& $libraries['webform.element.chosen']['dependencies'];
      foreach ($dependencies as $index => $dependency) {
        if ($dependency === 'webform/libraries.jquery.chosen') {
          $dependencies[$index] = 'chosen_lib/chosen';
          $dependencies[] = 'chosen_lib/chosen.css';
          break;
        }
      }
    }
  }

  // If select2.module is installed, then update the dependency.
  if (\Drupal::moduleHandler()->moduleExists('select2')) {
    if (isset($libraries['webform.element.select2'])) {
      $dependencies =& $libraries['webform.element.select2']['dependencies'];
      foreach ($dependencies as $index => $dependency) {
        if ($dependency === 'webform/libraries.jquery.select2') {
          $dependencies[$index] = 'select2/select2';
          break;
        }
      }
    }
  }

  /** @var \Drupal\webform\WebformLibrariesManagerInterface $libraries_manager */
  $libraries_manager = \Drupal::service('webform.libraries_manager');

  // Map /library/* paths to CDN.
  // @see webform.libraries.yml.
  foreach ($libraries as $library_name => &$library) {
    // Remove excluded libraries.
    if ($libraries_manager->isExcluded($library_name)) {
      unset($libraries[$library_name]);
      continue;
    }

    // Skip libraries installed by other modules.
    if (isset($library['module'])) {
      continue;
    }

    if (!empty($library['dependencies'])) {
      // Remove excluded libraries from dependencies.
      foreach ($library['dependencies'] as $dependency_index => $dependency_name) {
        if ($libraries_manager->isExcluded($dependency_name)) {
          $library['dependencies'][$dependency_index] = NULL;
          $library['dependencies'] = array_filter($library['dependencies']);
        }
      }
    }

    // Check CDN setting exists.
    if (!isset($library['cdn'])) {
      continue;
    }

    // Check if the CDN's source /library/* path exists.
    reset($library['cdn']);
    if (file_exists(DRUPAL_ROOT . key($library['cdn']))) {
      continue;
    }

    _webform_library_info_alter_recursive($library, $library['cdn']);
  }
}

/**
 * Recursive through a webform library.
 *
 * @param array $library
 *   A webform library defined in webform.libraries.yml.
 * @param array $cdn
 *   A associative array of library paths mapped to CDN URL.
 */
function _webform_library_info_alter_recursive(array &$library, array $cdn) {
  foreach ($library as $key => &$value) {
    // CSS and JS files and listed in associative arrays keyed via string.
    if (!is_string($key) || !is_array($value)) {
      continue;
    }

    // Ignore the CDN's associative array.
    if ($key == 'cdn') {
      continue;
    }

    // Replace the CDN sources (i.e. /library/*) with the CDN URL destination
    // (https://cdnjs.cloudflare.com/ajax/libs/*).
    foreach ($cdn as $source => $destination) {
      if (strpos($key, $source) === 0) {
        $uri = str_replace($source, $destination, $key);
        $library[$uri] = $value;
        $library[$uri]['type'] = 'external';
        unset($library[$key]);
        break;
      }
    }

    // Recurse downward to find nested libraries.
    _webform_library_info_alter_recursive($value, $cdn);
  }
}
