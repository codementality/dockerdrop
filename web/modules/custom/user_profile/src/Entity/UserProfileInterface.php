<?php

namespace Drupal\user_profile\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining User Profile entities.
 *
 * @ingroup user_profile
 */
interface UserProfileInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the User Profile name.
   *
   * @return string
   *   Name of the User Profile.
   */
  public function getName();

  /**
   * Sets the User Profile name.
   *
   * @param string $name
   *   The User Profile name.
   *
   * @return \Drupal\user_profile\Entity\UserProfileInterface
   *   The called User Profile entity.
   */
  public function setName($name);

  /**
   * Gets the User Profile creation timestamp.
   *
   * @return int
   *   Creation timestamp of the User Profile.
   */
  public function getCreatedTime();

  /**
   * Sets the User Profile creation timestamp.
   *
   * @param int $timestamp
   *   The User Profile creation timestamp.
   *
   * @return \Drupal\user_profile\Entity\UserProfileInterface
   *   The called User Profile entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the User Profile published status indicator.
   *
   * Unpublished User Profile are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the User Profile is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a User Profile.
   *
   * @param bool $published
   *   TRUE to set this User Profile to published, FALSE to set it to
   *   unpublished.
   *
   * @return \Drupal\user_profile\Entity\UserProfileInterface
   *   The called User Profile entity.
   */
  public function setPublished($published);

}
