<?php

namespace Drupal\advance_script_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ListscriptsForm.
 */
class ListscriptsForm extends FormBase {

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Header variable.
   *
   * @var array
   */

  protected $header;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->messenger = $container->get('messenger');
    $instance->database = $container->get('database');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'listscripts_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->header = [
      'script_name' => [
        'data' => $this->t('Script name'),
        'field' => 'script_name',
      ],
      'visibility_section' => [
        'data' => $this->t('visibility_section'),
        'field' => 'visibility_section',
      ],
      'created' => [
        'data' => $this->t('Created'),
        'field' => 'created',
      ],
      'updated' => [
        'data' => $this->t('Updated'),
        'field' => 'updated',
      ],
      'status' => [
        'data' => $this->t('Status'),
        'field' => 'status',
      ],
      'action' => $this->t('Action'),
    ];
    $tblrow = [];
    $scripts = $this->database->select('advance_script_manager', 'a')
      ->fields('a', [
        'id',
        'script_name',
        'visibility_section',
        'created',
        'updated',
        'status',
      ])
      ->condition('status', 1)
      ->orderBy('id', 'DESC')
      ->execute()
      ->fetchAll();
    foreach ($scripts as $row) {
      $edit = Url::FromUserInput('/admin/config/development/advance-script-manager/scripts?num=' . $row->id);
      $tblrow[$row->id] = [
        'script_name' => $row->script_name,
        'visibility_section' => $row->visibility_section,
        'created' => ($row->created ?: 'NULL'),
        'updated' => ($row->updated ?: 'NULL'),
        'status' => ($row->status == 1 ? $this->t('active') : $this->t('Disabled')),
        'action' => Link::fromTextAndUrl('Edit script', $edit),
      ];
    }

    $form['list_scripts'] = [
      '#type' => 'tableselect',
      '#header' => $this->header,
      '#options' => $tblrow,
      '#weight' => '0',
      '#empty' => $this->t('No records found'),
      '#attributes' => ['class' => ['advance-scripts-table']],
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
