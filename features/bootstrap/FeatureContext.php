<?php

use Behat\Behat\Tester\Exception\PendingException,
    Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\MinkExtension\Context\MinkContext,
    Behat\Behat\Context\SnippetAcceptingContext,
    Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends MinkContext implements SnippetAcceptingContext
{

    protected $assignment_title;
    /**
     * Initializes context.
     * Every scenario gets its own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

    /**
     * @Given I visit the App in Schoology
     */
    public function iVisitTheAppInSchoology()
    {
        $this->visit('https://isd.schoology.com/apps/370475071/run/course/312161093');
    }

    /**
     * @Given I log in using :email :password
     */
    public function iLogInUsing($email, $password)
    {
        $this->fillField('email',$email);
        $this->pressButton('next');
        $this->getSession()->wait(500);
        $this->fillField('Passwd',$password);
        $script="document.getElementById(\"signIn\").click();";
        $this->getSession()->executeScript($script);
        $this->getSession()->maximizeWindow();
        $this->getSession()->wait(20000);
    }

    /**
     * @When I sign in as a student
     */
    public function iSignInAsAStudent()
    {
        $this->fillField('email',env('STUDENT_EMAIL'));
        $this->pressButton('next');
        $this->getSession()->wait(1000);
        $this->fillField('Passwd',env('STUDENT_PASSWORD'));
        $script="document.getElementById(\"signIn\").click();";
        $this->getSession()->executeScript($script);
        $this->getSession()->maximizeWindow();
        $this->getSession()->wait(20000);
    }

    /**
     * @When I view the frame :iframe
     */
    public function IViewTheFrame($iframe)
    {
        $this->getSession()->switchToIFrame($iframe);
    }

    /**
     * @Given I am signed in as a Course Administrator
     */
    public function iAmSignedInAsACourseAdministrator()
    {
        $this->getSession()->maximizeWindow();
        $this->visit('https://isd.schoology.com');
        $this->fillField('email',env("COURSE_ADMIN_EMAIL"));
        $this->pressButton('next');
        $this->getSession()->wait(1500);
        $this->fillField('Passwd',env("COURSE_ADMIN_PASSWORD"));
        $script="document.getElementById(\"signIn\").click();";
        $this->getSession()->executeScript($script);
        $this->getSession()->wait(20000, 'document.getElementById("home-feed-container") != null');
    }

    /**
     * @Given I have created an Assignment in Schoology
     */
    public function iHaveCreatedAnAssignmentInSchoology()
    {
        $this->assignment_title=uniqid("assignment_");
        $this->visit("https://isd.schoology.com/course/".env("COURSE_ID")."/materials/assignments/add");
        $this->getSession()->wait(3000,'document.getElementById("edit-description_ifr") != null');
        $this->fillField("title",$this->assignment_title);
        $this->pressButton("op");
        $this->getSession()->wait(1000);
    }

    /**
     * @When I create a new Assignment in Drive Companion
     */
    public function iCreateANewAssignmentInDriveCompanion()
    {
        $this->iVisitTheAppInSchoology();
        $this->getSession()->wait(10000,'document.getElementById("schoology-app-container") != null;');
        $this->getSession()->switchToIFrame("schoology-app-container");
        $this->getSession()->wait(1000,'document.getElementById("create_assignment_btn") != null');
        $this->clickLink("create_assignment_btn");
        $this->fillField("assignment_name",$this->assignment_title);
        $this->getSession()->wait(10000);
    }

    /**
     * @Then I can attach it to the Schoology Assignment
     */
    public function iCanAttachItToTheSchoologyAssignment()
    {
        $this->selectOption('schoology_assignment',$this->assignment_title);
    }
}
