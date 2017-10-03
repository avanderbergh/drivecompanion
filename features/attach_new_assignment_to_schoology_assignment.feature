Feature: Attach new Assignment to Schoology Assignment
  In order to allow students to submit Google Drive Documents as a Schoology Assignment
  As a Course Administrator
  I want to attach a Drive Companion Assignment to an Assignment created in Schoology
  @javascript
  Scenario:
    Given I am signed in as a Course Administrator
    And I have created an Assignment in Schoology
    When I create a new Assignment in Drive Companion
    Then I can attach it to the Schoology Assignment