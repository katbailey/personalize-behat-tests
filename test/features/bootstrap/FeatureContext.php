<?php

use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalContext;


//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends DrupalContext
{
  /**
   * Keep track of agents so they can be cleaned up.
   *
   * @var array
   */
  public $agents = array();

  /**
   * Keep track of option sets so they can be cleaned up.
   *
   * @var array
   */
  public $optionSets = array();

  /**
   * Keep track of option sets so they can be cleaned up.
   *
   * @var array
   */
  public $actions = array();

  /**
   * Keep track of option sets so they can be cleaned up.
   *
   * @var array
   */
  public $goals = array();

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
   * @Given /^"(?P<type>[^"]*)" agents:$/
   */
  public function createAgents($type, TableNode $agentsTable) {
    foreach ($agentsTable->getHash() as $agentHash) {
      $agent = (object) $agentHash;
      $agent->plugin = $type;
      $data = array();
      if (!empty($agentHash['url_contexts'])) {
        $data['visitor_context'] = array(
          'querystring_context' => array()
        );
        $contexts = explode(',', $agentHash['url_contexts']);
        foreach ($contexts as $context) {
          $data['visitor_context']['querystring_context'][$context] = $context;
        }
      }
      $agent->data = $data;

      $saved = personalize_agent_save($agent);
      $this->agents[] = $saved;
      personalize_agent_set_status($agent->machine_name, PERSONALIZE_STATUS_RUNNING);
    }
  }

  /**
   * @Given /^URL context configuration:$/
   */
  public function createURLContextConfig(TableNode $config) {
    $contexts = array();
    foreach ($config->getHash() as $configHash) {
      $contexts[] = $configHash['name'];
    }
    variable_set('personalize_url_querystring_contexts', $contexts);
  }

  /**
   * @Given /^personalized elements:$/
   */
  public function createPersonalizedElements(TableNode $elementsTable) {
    foreach ($elementsTable->getHash() as $optionSetHash) {
      $option_set = (object) $optionSetHash;
      $option_set->plugin = 'elements';
      $option_set->data = array(
        'personalize_elements_selector' => $option_set->selector,
        'personalize_elements_type' => $option_set->type,
      );
      $option_set->executor = 'personalizeElements';
      $content_options = explode(',', $option_set->content);
      $options = array();
      $context_values = array();
      // Grab explicit targeting values if specified.
      if (!empty($option_set->targeting)) {
        $contexts = variable_get('personalize_url_querystring_contexts', array());
        if (in_array($option_set->targeting, $contexts)) {
          foreach (array('first-value', 'second-value', 'third-value') as $value) {
            $context_values[] = $option_set->targeting . '::' . $value;
          }
        }
      }
      foreach ($content_options as $index => $content) {
        $content = trim($content);
        $option = array(
          'option_label' => personalize_generate_option_label($index),
          'personalize_elements_content' => $content,
        );
        // Set up fixed targeting if there's an available fixed targeting value.
        if (!empty($context_values)) {
          $option['fixed_targeting'] = array(array_shift($context_values));
        }
        $options[] = $option;
      }
      $options = personalize_ensure_unique_option_ids($options);
      $control_option = array('option_label' => PERSONALIZE_ELEMENTS_CONTROL_OPTION_LABEL, 'option_id' => PERSONALIZE_ELEMENTS_CONTROL_OPTION_ID, 'personalize_elements_content' => '');
      array_unshift($options, $control_option);
      $option_set->options = $options;
      $saved = personalize_option_set_save($option_set);
      personalize_agent_set_status($option_set->agent, PERSONALIZE_STATUS_RUNNING);
      $this->optionSets[] = $saved;
    }
  }

  /**
   * @Given /^campaign goals:$/
   */
  public function createCampaignGoals(TableNode $campaign_goals) {
    foreach ($campaign_goals->getHash() as $goalHash) {
      // First create the action.
      $action = $goalHash;
      $agent = $goalHash['agent'];
      $value = $goalHash['value'];
      unset($action['agent'], $action['value']);
      $action['data'] = array();
      $action['pages'] = '';
      visitor_actions_save_action($action);
      $this->actions[] = $action;
      personalize_goal_save($agent, $action['machine_name'], $value);
      $goal = db_select('personalize_campaign_goals', 'g')
        ->fields('g')
        ->condition('action', $action['machine_name'])
        ->condition('agent', $agent)
        ->execute()
        ->fetchObject();
      $this->goals[] = $goal;
    }
  }

  /**
   * @When /^I wait for the Acquia Lift controls box to appear$/
   */
  public function waitForAcquiaLiftControls() {
    $this->getSession()->wait(5000, "jQuery('.acquia-lift-controls-dialog').length > 0");
  }

  /**
   * @When /^I wait for the option controls to appear$/
   */
  public function waitForPreviewControls() {
    $this->getSession()->wait(5000, "jQuery('.acquia-lift-controls ul.acquia-lift-option-sets li:first').is(':visible') === true");
  }

  /**
   * @Given /^visitor context caching is enabled$/
   */
  public function enableVisitorContextCaching() {
    variable_set('personalize_cache_visitor_context', 1);
  }

  /**
   * @Then /^there should be (\d+) "([^"]*)" goals for agent "([^"]*)"$/
   */
  public function assertNumGoalsForAgent($num, $goal_name, $agent_name) {
    global $conf;
    cache_clear_all('variables', 'cache_bootstrap');
    $conf = variable_initialize();
    $current_goals = variable_get('personalize_test_goals_received', array());
    if (!isset($current_goals[$agent_name])) {
      $shiz = serialize($current_goals);
      throw new \Exception(sprintf("No goals found for %s", $shiz));
    }
    if (!isset($current_goals[$agent_name][$goal_name])) {
      throw new \Exception(sprintf("No %s goals found for %s", $goal_name, $agent_name));
    }
    $total_goals = 0;
    foreach ($current_goals[$agent_name][$goal_name] as $session => $value) {
      $total_goals += $value;
    }
    if ($total_goals != $num) {
      throw new \Exception(sprintf("Expected value of %s goals for %s was %d, actual value is %d", $goal_name, $agent_name, $num, $total_goals));
    }
  }

  /**
   * @When /^I wait for the page to load$/
   */
  public function waitForPageToLoad() {
    $this->getSession()->wait(10000, '0 === jQuery.active');
  }

  /**
   * Run after every scenario.
   *
   * @AfterScenario
   */
  public function cleanUpPersonalizationFixtures($event) {
    // Remove any agents that were created.
    if (!empty($this->agents)) {
      foreach ($this->agents as $agent) {
        personalize_agent_delete($agent->machine_name);
      }
    }
    // Remove any option sets that were created.
    if (!empty($this->optionSets)) {
      foreach ($this->optionSets as $option_set) {
        personalize_option_set_delete($option_set->osid);
      }
    }
    // Remove any actions that were created.
    if (!empty($this->actions)) {
      foreach ($this->actions as $action) {
        visitor_actions_delete_action($action['machine_name']);
      }
    }
    // Remove any goals that were created.
    if (!empty($this->goals)) {
      foreach ($this->goals as $goal) {
        personalize_goal_delete($goal->id);
      }
    }
    variable_set('personalize_url_querystring_contexts', array());
    variable_set('personalize_test_goals_received', array());
  }

}
