Feature: List Drafts
  In order to make operations on drafts
  As a user with specific privileges
  I need to have a list of drafts

  Scenario: List Drafts
    Given There is a draft with status "New"
    When I send a "GET" request to "/rest/drafts/?status=1&page=1" endpoint
    Then The response status code should be 200
    Then The response matches expected template:
    """
    {
       "objects": [ "@json@" ],
       "params":"@json@"
    }
    """

