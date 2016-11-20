Feature: Checks Roles and Permissions.
  As a developer
  I need to be able to tell if created roles have the right permissions

  # Scenario 1
  @javascript @api @failing
  Scenario Outline: Check that proper roles exist
    Given I am logged in as a user with the "administrator" role
    Then I can see that the "<role>" role exists
    Examples:
      | role |
      | free_member |
      | paid_member |
      | lifetime_member |
      | affiliate_member |
      | site_administrator |

  # Scenario 2
  @javascript @api @failing
  Scenario Outline: Check the "staff" role has the proper permissions
    Given I am logged in as a user with the "administrator" role
    Then I can see that the "<role>" role has all granted permissions
    Examples:
    | role |
    | free_member |
    | paid_member |
    | lifetime_member |
    | affiliate_member |
    | authenticated |
    | anonymous |

  # Scenario 3
  @javascript @api @failing
  Scenario: Check the "site_administrator" role has all permissions
    Given I am logged in as a user with the "site_administrator" role
    Then I can see that the "site_administrator" role has all available permissions
