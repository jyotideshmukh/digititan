<?php

namespace Drupal\hackathon_event\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;

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
      '#title' => $this->t('Leader Name'),
      '#attributes' => ['class' => ['MYCUSTOM-autocomplete'],],
      '#autocomplete_route_name' => 'hackathon_event.user_autocomplete',
      '#description' => $this->t('Please add leader name'),
      '#required' => true,
    ];
    $no_members = [0, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5];
    $form['members_count'] = [
      '#type' => 'select',
      '#title' => $this->t('Add Memebers'),
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
    if ($form_state->hasValue('members_count')) {
      $members = $form_state->getValue('members_count');
    } else {
      $members = 0;
    }
    for ($i=1; $i <= $members; $i++) {
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

    $logo_file = File::load( $form_state->getValue('logo_file')[0] );
    $logo_file->setPermanent();
    $logo_file->save();

    $text = $form_state->getValue('leader_uid');
    preg_match('#\((.*?)\)#', $text, $match);
    $leader_uid = $match[1];

    $fields = [
      'name' => $form_state->getValue('name'),
      'event_id' => $form_state->getValue('event_id'),
      'logo_file' => $form_state->getValue('logo_file')[0],
      'platform_account' => $form_state->getValue('platform_account'),
      'platform_repository' => $form_state->getValue('platform_repository'),
      'leader_uid' => $leader_uid,
      'status' => 1,
      'created' => time(),
      'created_by' => \Drupal::currentUser()->id(),
    ];
    
    try {
      $team_id = \Drupal::database()->insert('hackathon_team')
      ->fields($fields)
      ->execute();
      $team_uids[] = [
        'team_id' => $team_id,
        'uid' => $leader_uid
      ];
      $members_count = $form_state->getValue('members_count');
      for ($i=0;$i<$members_count;$i++) {
        $user_data = $form_state->getValue("member$i");
        preg_match('#\((.*?)\)#', $user_data, $match);
        $team_uids[] = [
          'team_id' => $team_id,
          'uid' => $match[1]
        ];
      }
      $query = \Drupal::database()->insert('hackathon_team_user')->fields(['team_id', 'uid']);
      foreach ($team_uids as $team_uid) {
        $query->values($team_uid);
      }
      $query->execute();
    } catch (\Exception $e) {
      \Drupal::logger("hackathon_event")->error("Team Insert: ".$e->getMessage());
      $form_state->setRedirect('<front>');
    }
      $service = \Drupal::service('gittest.Gitservices');
      $service->createRepo($teamname);
      drupal_set_message($this->t("Team added successfully."));
  }

}
