<?php

/**
 * @file
 * RolesPermissionsContext class.
 */

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;

/**
 * Defines application features from the specific context.
 */
class RolesPermissionsContext extends RawDrupalContext implements SnippetAcceptingContext {

  private $rolesAndPermissions;

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct(array $roles_and_permissions) {
    $this->rolesAndPermissions = $roles_and_permissions;
  }

  /**
   * Test if role exists.
   *
   * @Then I can see that the :role role exists
   *
   * This function is a custom Gerkin step used in the Roles & Permissions
   * testing in the "roles-permissions.feature" test file.
   */
  public function iCanSeeThatTheRoleExists($role) {
    $role_exists = FALSE;
    $drupal_roles = user_roles(TRUE);

    if (defined('VERSION') && constant('VERSION') >= '7' && constant('VERSION') < '8') {
      foreach ($drupal_roles as $rid => $drupal_role) {
        if ($role == $drupal_role) {
          $role_exists = TRUE;
        }
      }
    }
    elseif (constant('Drupal::VERSION') >= '8') {
      foreach ($drupal_roles as $drupal_role => $role_class) {
        if ($role == $role_class->id()) {
          $role_exists = TRUE;
        }
      }
    }
    if (!$role_exists) {
      throw new Exception(sprintf('Role %s does not exist!', $role));
    }

  }

  /**
   * Checks that a role has all granted permissions, as outlined in behat.yml.
   *
   * @Then I can see that the :role role has all granted permissions
   */
  public function iCanSeeThatTheRoleHasAllGrantedPermissions($role) {
    // Get permissions to check passed in from behat.yml.
    $permissions_to_check = ($this->rolesAndPermissions[$role]);
    if (defined('VERSION') && constant('VERSION') >= '7' && constant('VERSION') < '8') {
      // Convert any taxonomy permissions by vocabulary to vocabulary IDs.
      foreach ($permissions_to_check as $key => $perm) {
        $perm = $this->modifyPermission($perm);
        $permissions_to_check[$key] = $perm;
      }
    }
    // Sort in order by value.
    asort($permissions_to_check);
    // Get actual permissions granted to this role.
    $role_permissions = $this->getPermissionsForRole($role);
    // Create a missing permissions array.
    $missing_perms = array_diff($permissions_to_check, $role_permissions);
    // Check that all permissions for the role are in the list to check, if not
    // throw an Exception and list the missing permissions.
    if (!empty($missing_perms)) {
      $missing_perms_string = implode(", ", $missing_perms);
      throw new Exception(sprintf('The %s role is missing these permissions: %s', $role, $missing_perms_string));
    }
    // Get all available permissions for the drupal installation.
    $all_permissions = $this->getAllAvailablePermissions();
    // Get the permissions not granted to this role.
    $all_less_role_permissions = array_diff($all_permissions, $role_permissions);
    // Get the permissions that this role is not supposed to have.
    $all_less_checked_permissions = array_diff($all_permissions, $permissions_to_check);
    // Get any differences.
    $extra_permissions = array_diff($all_less_checked_permissions, $all_less_role_permissions);
    // If there are differences (array is not empty), throw and Exception and
    // list the extra permissions.
    if (!empty($extra_permissions)) {
      $extra_perms = implode(", ", $extra_permissions);
      throw new Exception(sprintf('The %s role has extra permissions: %s', $role, $extra_perms));
    }
  }

  /**
   * Checks that a role has all available permissions.
   *
   * @Then I can see that the :role role has all available permissions
   */
  public function iCanSeeThatTheRoleHasAllAvailablePermissions($role) {
    $role_permissions = $this->getPermissionsForRole($role);
    // Gets all available permissions in this drupal installation.
    $all_permissions = $this->getAllAvailablePermissions();
    // Get the permissions not granted to this role.
    $all_less_role_permissions = array_diff($all_permissions, $role_permissions);

    // Compare the sorted array values, if they aren't equal, throw Exception.
    if (!empty($all_less_role_permissions)) {
      throw new Exception(sprintf('Role %s does not have all available permissions.', $role));
    }
  }

  /**
   * Converts taxonomy permission strings to use vocabulary ids (Drupal 7).
   *
   * @param string $permission
   *   The original permissions string.
   *
   * @return string
   *   The modified permissions string.
   */
  private function modifyPermission($permission) {
    if (strpos($permission, ' terms in ') > 0) {
      $vocabulary_name = substr($permission, strpos($permission, ' terms in ') + strlen(' terms in '));
      $vocabularies = taxonomy_vocabulary_get_names();
      $vocabulary = taxonomy_vocabulary_machine_name_load($vocabulary_name)->vid;
      return substr($permission, 0, strpos($permission, ' terms in ') + strlen(' terms in ')) . $vocabulary;
    }
    return $permission;
  }

  /**
   * Returns an array of all available permissions.
   *
   * @return array
   *   All available permissions.
   */
  private function getAllAvailablePermissions() {
    // Builds a list of all permissions by module, keyed by permission.
    if (defined('VERSION') && constant('VERSION') >= '7' && constant('VERSION') < '8') {
      $permissions_by_role = user_permission_get_modules();
      // Pulls out just the keys, which are the permissions.
      $permissions = array_keys($permissions_by_role);
    }
    elseif (constant('Drupal::VERSION') >= '8') {
      $permissions = array_keys(\Drupal::service('user.permissions')->getPermissions());
    }
    // Sort the array by value.
    asort($permissions);
    // Returns the array.
    return $permissions;
  }

  /**
   * Returns an array of permissions granted to a role.
   *
   * @param string $role
   *   The role to check.
   * @param bool $inherited
   *   Boolean, also returns permissions for authenticated & anonymous
   *     user roles if TRUE.
   *
   * @return array
   *   The array of permissions granted to the role, in alphabetical order.
   */
  private function getPermissionsForRole($role, $inherited = TRUE) {
    if ($role == 'anonymous user' || $role == 'anonymous') {
      $inherited = FALSE;
    }
    // Gets all user roles from drupal.
    $roles = user_roles();
    if (defined('VERSION') && constant('VERSION') >= '7' && constant('VERSION') < '8') {

      // Gets the role ID for the role we are checking.
      $rid = array_search($role, $roles);
      if ($inherited) {
        $rid2 = array_search('authenticated user', $roles);
        $rid3 = array_search('anonymous user', $roles);
      }
      // Gets the permissions for the role we are checking, keyed by $rid.
      if ($inherited) {
        $role_perms = user_role_permissions(
          array(
            $rid => $role,
            $rid2 => 'authenticated user',
            $rid3 => 'anonymous user',
          )
        );
      }
      else {
        $role_perms = user_role_permissions(array($rid => $role));
      }
      // Pulls just the permissions for $rid.
      $role_permissions = array_keys($role_perms[$rid]);
      if ($inherited && $role != 'anonymous user') {
        // Pulls permissions for authenticated users.
        $auth_permissions = array_keys($role_perms[$rid2]);
        // Combines the two arrays.
        $role_permissions = array_merge($auth_permissions, $role_permissions);
        // Removes the unique values, leaving one distinct value.
        array_unique($role_permissions);
        // Pulls the permissions for anonymous users.
        $anon_permissions = array_keys($role_perms[$rid3]);
        // Combines the two arrays.
        $role_permissions = array_merge($anon_permissions, $role_permissions);
        // Removes the unique values, leaving one distinct value.
        array_unique($role_permissions);

      }
    }
    elseif (constant('Drupal::VERSION') >= '8') {
      if ($inherited) {
        $rid2 = 'authenticated';
        $rid3 = 'anonymous';
      }
      // Gets the permissions for the role we are checking, keyed by $rid.
      if ($inherited) {
        $role_perms = user_role_permissions(
          array(
            $role,
            $rid2,
            $rid3,
          )
        );
      }
      else {
        $role_perms = user_role_permissions(
          array($role)
        );
      }
      $role_permissions = array();
      foreach ($role_perms as $key => $perms) {
        foreach ($perms as $perm) {
          $role_permissions[] = $perm;
        }
      }
      $role_permissions = array_unique($role_permissions);
    }
    // Sorts the array by value.
    asort($role_permissions);
    return $role_permissions;
  }

}
