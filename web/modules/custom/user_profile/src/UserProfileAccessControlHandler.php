<?php

namespace Drupal\user_profile;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the User Profile entity.
 *
 * @see \Drupal\user_profile\Entity\UserProfile.
 */
class UserProfileAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\user_profile\Entity\UserProfileInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished user profile entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published user profile entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit user profile entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete user profile entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add user profile entities');
  }

}
