Feature: Creating rule
  As a Catalog Manager
  When I send an empty form
  I want to get errors

  @javascript
  Scenario:
    Given I log in as a test user
    And I wait and follow link "System"
    And I wait and follow link "Rules"
    And I click on create rule
    And I save
    Then I should get 4 errors

    
