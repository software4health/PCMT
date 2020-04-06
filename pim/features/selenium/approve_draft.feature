Feature: Approving Drafts
  In order to introduce the desired changes to the product
  As a Catalog Manager
  I want to be able to approve a draft that includes latest changes for the product

  @javascript
  Scenario:
    Given There is 1 quantity of drafts with status "New"
    And I log in as a test user
    When I follow "Drafts"
    And wait for the page to load
    And I follow "Approve draft"
    When I confirm approval
    And wait for the page to load
    And I follow "Products"
    And wait for the page to load
    Then I should see my draft becoming the latest version of the product