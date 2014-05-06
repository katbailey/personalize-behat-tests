Feature: Acquia Lift

  Background:
    Given "article" nodes:
    | title           | body        | published on       | status | promote |
    | Test article    | PLACEHOLDER | 04/27/2013 11:11am |      1 |       1 |
    | Second article  | PLACEHOLDER | 04/27/2013 11:11am |      1 |       1 |
    And "personalize_target" agents:
    | machine_name        | label           | url_contexts    |
    | targeting-agent     | Targeting Agent | some-param      |
    And URL context configuration:
    | name          | value type     |
    | some-param    | string         |
    | other-param   | string         |
    And personalized elements:
    | label                     | agent           | selector                | type        | content         | stateful  | targeting   |
    | Personalized node title   | targeting-agent | a[title="Test article"] | replaceHtml | ohai,wtf,blarg  | 0         | some-param  |
    And campaign goals:
    | machine_name       | label              | plugin  | event   | identifier                | client_side   | agent       | value |
    | clicks-second-node | Second node click  | link    | click   | a[title="Second article"] | 1             | test-agent  | 2     |
    And visitor context caching is enabled


  @api @javascript
  Scenario: View Targeted Content
    Given I am not logged in
    And I am on "/"
    Then I should see "Test article"
    When I visit "/?some-param=first-value"
    Then I should see "ohai"
    When I visit "/"
    Then I should see "ohai"
    When I visit "/?some-param=second-value"
    Then I should see "wtf"
    When I visit "/"
    Then I should see "wtf"
    And I should not see "ohai"
    When I visit "/?some-param=third-value"
    Then I should see "blarg"
    When I visit "/"
    Then I should see "blarg"
    And I should not see "wtf"
    And I should not see "ohai"
    Then print last response

  @api @javascript
  Scenario: Use Acquia Lift Preview Controls
    Given I am logged in as a user with the "administrator" role
    Then print last response
    And I am on "/"
    Then I should see "Test article"
    When I click "toolbar-link-admin-acquia_lift"
    And I wait for the Acquia Lift controls box to appear
    When I click "All campaigns"
    And I click "My Test Agent"
    And I click "Goals"
    And I wait for the option controls to appear
    Then I should see "Second node click"
    When I click "All campaigns"
    And I click "Targeting Agent"
    And I click "Content variations"
    And I wait for the option controls to appear
    And I click "Preview Option A"
    Then I should see "ohai"
    When I click "Preview Option B"
    Then I should see "wtf"
    When I click "Preview Option C"
    Then I should see "blarg"
    When I click "Preview Control Option"
    Then I should see "Test article"


  @api @javascript
  Scenario: Send client-side goals
    Given I am not logged in
    And I am on "/"
    When I click "Read more"
    And I wait for the page to load
    And I click "Home"
    And I wait for the page to load
    And I click "Read more"
    And I wait for the page to load
    And I click "Home"
    And I wait for the page to load
    And I click "Read more"
    And I wait for the page to load
    And I click "Home"
    And I wait for the page to load
    Then there should be 6 "clicks-second-node" goals for agent "test-agent"
