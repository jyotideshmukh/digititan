<?php

namespace Drupal\hackathon_event\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;


/**
 * Class TeamForm.
 */
class TeamForm extends FormBase {

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
    return 'hackathon_event_team_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $event_id = 0) {
    $user_data = User::loadMultiple([1]);
    foreach($user_data as $user) {
      print_r($user->id());
      print_r($user->name);
    }
    die;
// print_r(User::loadMultiple([1]));

    $event_id = 1;
    $hackathon_file = \Drupal::config('hackathon_event.file');
    $max_file_size = $hackathon_file->get('max_file_size');
    // public function buildForm(array $form, FormStateInterface $form_state, $event_id = null) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Team Name'),
      '#description' => $this->t('Please enter name of the team. Aphanumeric values are allowed for event name and must be at least 5 characters and max 30 characters in length.'),
      '#maxlength' => 30,
      '#size' => 31,
    ];
    $form['event_id'] = array(
      '#type' => 'hidden',
      '#value' => $event_id,
    );
    $form['logo_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Add Team Logo'),
      '#upload_location' => 'public://',
      '#upload_validators' => [
          'file_validate_extensions' => [ 'gif png jpg jpeg' ],
          'file_validate_size' => [ $max_file_size ],
      ],
    ];
    $form['platform_account'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Github Account Name'),
      '#description' => $this->t('Please enter github platform account name. Github Account Url: https://github.com/TempAccount/ Then platform account name will be TempAccount'),
      '#maxlength' => 30,
      '#size' => 31,
    ];
    $form['platform_repository'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Github Repository Name'),
      '#description' => $this->t('Please enter github platform repository name. Github Account Url: https://github.com/TempAccount/firstRepo.git Then repository name will be firstRepo'),
      '#maxlength' => 30,
      '#size' => 31,
    ];
    $form['leader_uid'] = [
      '#type' => 'textfield',
      '#title' => t('Name of the referenced node'),
      '#autocomplete_route_name' => 'mymodule.autocomplete',
      '#description' => t('Node Add/Edit type block'),
      '#default' => ($form_state->isValueEmpty('nid')) ? null : ($form_state->getValue('nid')),
      '#required' => true,
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      // '#default_value' => $owner->isAnonymous() ? NULL : $owner,
      // A comment can be made anonymous by leaving this field empty therefore
      // there is no need to list them in the autocomplete.
      '#selection_settings' => [
        'include_anonymous' => FALSE,
      ],
      '#title' => $this->t('Leader Name'),
      '#description' => $this->t('Please add leader name'),
    ];
    $no_members = [
      1 => 1,
      2 => 2,
      3 => 3,
      4 => 4,
      5 => 5,
    ];
    if (!empty($form_state->getValue('member_count'))) {
       // Get the value if it already exists.
       $members = $form_state->getValue('member_count');
    }
    else {
     // Use a default value.
     $members = 1;
    }
    \Drupal::logger("priyanka")->error($members);
    $form['members_count'] = [
      '#type' => 'select',
      '#title' => $this->t('How many members you wanted to add in team?'),
      '#options' => $no_members,
      '#ajax' => [
        'callback' => '::addMemberCallback',
        'wrapper' => 'member-fieldset-wrapper',
        'event' => 'change',
      ],
    ];
    $form['member_fieldset'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'member-fieldset-wrapper'],
    ];
    for ($i=0; $i < $members; $i++) {
      $form['member_fieldset']["member$i"] = [
        '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
        '#selection_settings' => [
          'include_anonymous' => FALSE,
        ],
        '#title' => $this->t('Member Name: '. $i),
        '#description' => $this->t('Please add member name'),
      ];
    }
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * ajax callback to create member
   */
  public function addMemberCallback(array &$form, FormStateInterface $form_state) {
    return $form['member_fieldset'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      $this->messenger->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }
  }

}
