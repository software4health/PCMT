Feature: API GET requests for products

  Scenario: List products
    Given I receive token
    When I send a "GET" request to "/api/rest/v1/products" endpoint
    Then The response status code should be 200

  Scenario: Get one product
    Given I receive token
    When I send a "GET" request to "/api/rest/v1/products/R_651" endpoint
    Then The response status code should be 200
