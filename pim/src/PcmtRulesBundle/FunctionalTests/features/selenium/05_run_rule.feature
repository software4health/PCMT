Feature: Running a rule
  As a Catalog Manager
  When I click to run a rule
  I want to see that the rule was processed

  @javascript
  Scenario:
    Given I log in as a test user
    When I wait and follow link "System"
    And I wait and follow link "Rules"
    And I wait and click run on last rule
    Then I should get success message
    Then first job on the list should be "PCMT rule process" with status ">Completed<"
    When I click on first job
    Then I should see the "Attributes found" row with value "2"






    
