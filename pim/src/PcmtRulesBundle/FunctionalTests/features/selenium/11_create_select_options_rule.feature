Feature: Creating a 'select options' rule
  As a Catalog Manager
  When I send filled out form
  I want to have a rule created

  @javascript
  Scenario:
    Given I log in as a test user
    When I wait and follow link "Rules"
    And I click on create rule
    And I fill in "code" with timestamp
    And I fill in "label" with "test create options rule"
    And I click on select field no 1
    And I choose "Pcmt Select options rule" option
    And I save create form
    Then I should get success message


    
