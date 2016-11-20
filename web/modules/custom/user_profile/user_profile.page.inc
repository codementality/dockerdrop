<?php

/**
 * @file
 * Contains user_profile.page.inc.
 *
 * Page callback for User Profile entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for User Profile templates.
 *
 * Default template: user_profile.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_user_profile(array &$variables) {
  // Fetch UserProfile Entity Object.
  $user_profile = $variables['elements']['#user_profile'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
