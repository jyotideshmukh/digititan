<?php

namespace Drupal\hackathon_event\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\file\Entity\File;

/**
 * Class EventForm.
 */
class EventForm extends FormBase {

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->messenger = $container->get('messenger');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hackathon_event_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $hackathon_file = \Drupal::config('hackathon_event.file');
    $max_file_size = $hackathon_file->get('max_file_size');
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Name'),
      '#description' => $this->t('Please enter name of the event. Aphanumeric values are allowed for event name and must be at least 5 characters and max 30 characters in length.'),
      '#maxlength' => 30,
      '#size' => 31,
      '#required' => true,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('Please enter description of event.'),
    ];
    $form['event_start_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Event Start Date'),
      '#description' => $this->t('Please enter event start date'),
      '#size' => 20,
      '#required' => true,
      '#default_value' => DrupalDateTime::createFromTimestamp(time())
    ];
    $form['event_end_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Event End Date'),
      '#description' => $this->t('Please enter event end date'),
      '#size' => 20,
      '#required' => true,
      '#default_value' => DrupalDateTime::createFromTimestamp(time())
    ];
    $form['logo_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Add Event logo'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
          'file_validate_extensions' => [ 'gif png jpg jpeg' ],
          'file_validate_size' => [ $max_file_size ],
      ],
      '#required' => true,
    ];
    $form['rules_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Add Rules and Regulations'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
          'file_validate_extensions' => [ 'pdf docx doc' ],
          'file_validate_size' => [ $max_file_size ],
      ],
      '#required' => true,
    ];
    $form['problem_statement_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Add Problem Statement'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
          'file_validate_extensions' => [ 'pdf docx doc' ],
          'file_validate_size' => [ $max_file_size ],
      ],
    ];
    $form['reg_start_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Registration Start Date'),
      '#description' => $this->t('Please enter registration start date'),
      '#size' => 20,
      '#required' => true,
      '#default_value' => DrupalDateTime::createFromTimestamp(time())
    ];
    $form['reg_end_date'] = [
      '#type' => 'datetime',
      '#title' => $this->t('Registration End Date'),
      '#description' => $this->t('Please enter registration start date'),
      '#size' => 20,
      '#required' => true,
      '#default_value' => DrupalDateTime::createFromTimestamp(time())
    ];
    $form['reg_fee'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Registration Fee'),
      '#description' => $this->t('Please enter registration fee. For free event it will be 0. Amount Added will be consider in Rupees for now.'),
      '#maxlength' => 10,
      '#size' => 10,
    ];
    $form['team_member_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum Team Member Size for Event'),
      '#description' => $this->t('Please enter team member size for each team including Leader'),
      '#maxlength' => 10,
      '#size' => 10,
      '#default_value' => '1',
    ];
    $form['event_teams'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum allowed teams'),
      '#description' => $this->t('Please enter maximum number of teams allowed for this event. Note that 0 will considered as any number of teams can participate in this event.'),
      '#maxlength' => 10,
      '#size' => 10,
      '#default_value' => '5',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $event_start_date = $form_state->getValues('event_start_date')['event_start_date']->getTimestamp();
    $event_end_date = $form_state->getValues('event_end_date')['event_end_date']->getTimestamp();
    $reg_start_date = $form_state->getValues('reg_start_date')['reg_start_date']->getTimestamp();
    $reg_end_date = $form_state->getValues('reg_end_date')['reg_end_date']->getTimestamp();
    $file = File::load( $form_state->getValue('rules_file')[0] );
    $file->setPermanent();
    $file->save();
    $logo_file = File::load( $form_state->getValue('logo_file')[0] );
    $logo_file->setPermanent();
    $logo_file->save();

    $fields = [
      'name' => $form_state->getValue('name'),
      'description' => $form_state->getValue('description'),
      'event_start_date' => $event_start_date,
      'event_end_date' => $event_end_date,
      'reg_start_date' => $reg_start_date,
      'reg_end_date' => $reg_end_date,
      'rules_file' => $form_state->getValue('rules_file')[0],
      'logo_file' => $form_state->getValue('logo_file')[0],
      'fee' => $form_state->getValue('reg_fee'),
      'per_team_size' => $form_state->getValue('team_member_size'),
      'event_teams' => $form_state->getValue('event_teams'),
      'status' => 1,
      'created' => time(),
      'created_by' => \Drupal::currentUser()->id(),
    ];
    if (!empty($form_state->getValue('problem_statement_file'))) {
      $ps_file = File::load( $form_state->getValue('problem_statement_file')[0] );
      $ps_file->setPermanent();
      $ps_file->save();
      $fields['problem_statement_file'] = $form_state->getValues['problem_statement_file'][0];
    }

    $result = $this->add_event($fields);
    if ($result) {
      drupal_set_message($this->t("Event added successfully."));
    }
  }

  private function add_event($fields) {
    try {
      \Drupal::database()->insert('hackathon_event')
        ->fields($fields)
        ->execute();
    } catch (\Exception $e){
      \Drupal::logger("hackathon_event")->error("Event Insert".$e->getMessage());
      return false;
    }
    return true;
  }

}
