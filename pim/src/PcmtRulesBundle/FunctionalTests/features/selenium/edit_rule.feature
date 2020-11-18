Feature: Editing a rule
  As a Catalog Manager
  When I click on a rule and send filled out form
  I want to be able to update a rule

  @javascript
  Scenario:
    Given I log in as a test user
    When I wait and follow link "System"
    And I wait and follow link "Rules"
    And I read number of rules
    And I wait and click edit on last draft
    And I wait
    And I fill in "unique_id" with ""
    And I save edit form
    Then I should get 1 errors
    And I fill in "unique_id" with timestamp
    And I click on source family
    And I choose "MD - HUB" option
    And I click on destination family
    And I choose "MD - HUB" option
    And I save edit form
    Then I should get error message
    And I click on source family
    And I choose "MD - HUB" option
    And I click on destination family
    And I choose "MD - RECIPIENT MAPPING" option
    And I save edit form
    Then I should get success message




    
