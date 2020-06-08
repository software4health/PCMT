Feature: Adding permissions
  In order limit access to Categories
  As a Catalog Manager
  I want to be able assign specific access levels for individual user.

  @javascript
  Scenario:
    Given I log in as a test user
    And I wait and follow link "Settings"
    And I wait and follow link "Categories"
    And I go to 1 category tree child
    And I wait and follow link "Permissions"
    When I edit permissions with parameters Redactor Redactor Redactor
    And I save
    Then I should see correct permissions set

    
