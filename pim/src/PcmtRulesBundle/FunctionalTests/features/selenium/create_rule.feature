Feature: Creating rule
  As a Catalog Manager
  When I send filled out form
  I want to have a rule created

  @javascript
  Scenario:
    Given I log in as a test user
    When I wait and follow link "System"
    And I wait and follow link "Rules"
    And I click on create rule
    And I fill in "unique_id" with timestamp
    And I follow "Choose the family"
    And I choose "MD - HUB" option
    And I follow "Choose the family"
    And I choose "MD - RECIPIENT MAPPING" option
    And I wait
    And I follow "Choose the attribute"
    And I choose "MD COUNTRY" option
    And I save
    Then I should get success message


    
