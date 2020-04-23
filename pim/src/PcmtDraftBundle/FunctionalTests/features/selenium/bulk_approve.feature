Feature: Bulk Approve
  In order to save time on approving drafts
  As a Catalog Manager
  I want to be able to select and bulk approve drafts

  @javascript
  Scenario:
    Given There is 1 quantity of drafts with status "New"
    And I log in as a test user
    When I follow "Drafts"
    And wait for the page to load
    When I select 1 draft checkboxes for mass action
    And I follow "Bulk approve"
    When I confirm approval
    When I follow "Products"
    And wait for the page to load
    Then I should see my draft becoming the latest version of the product, try 4 times