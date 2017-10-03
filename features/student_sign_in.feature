Feature: Student Sign In
  In order to view my dashboard
  As a student signed into Schoology
  I must be able to open the App through Schoology

  @javascript
  @insulated
  Scenario:
    When I visit the App in Schoology
    And I sign in as a student
    And I view the frame "schoology-app-container"
    Then I should see "My Drive"