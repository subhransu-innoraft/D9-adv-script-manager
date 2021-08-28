<?php

namespace Drupal\advance_script_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ListscriptsForm for listing scripts.
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
   * The current request on url.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->messenger = $container->get('messenger');
    $instance->database = $container->get('database');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->requestStack = $container->get('request_stack');
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
      'script_name' => $this->t('Script name'),
      'visibility_section' => $this->t('visibility section'),
      'created' => $this->t('Created'),
      'updated' => $this->t('Updated'),
      'status' => $this->t('Status'),
      'action' => $this->t('Action'),
    ];
    // Get parameter value while submitting filter form.
    $visibility = $this->requestStack->getCurrentRequest()->query->get('visibility');
    $status = $this->requestStack->getCurrentRequest()->query->get('status');
    $tblrow = $this->getRecords($visibility, $status);

    $form['list_scripts'] = [
      '#type' => 'tableselect',
      '#header' => $this->header,
      '#options' => $tblrow,
      '#weight' => '0',
      '#empty' => $this->t('No records found'),
      '#attributes' => ['class' => ['advance-scripts-table']],
    ];
    $form['pager'] = [
      '#type' => 'pager',
      '#prefix' => '<div class="adv-pagination">',
      '#suffix' => '</div>',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete selected scripts'),
    ];

    $form['bulk_deactivate'] = [
      '#type' => 'submit',
      '#value' => $this->t('Deactivate selected scripts'),
      '#submit' => [
        '::deactivateSelected',
      ],

    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @todo Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getRecords($visibility = NULL, $status = NULL) {
    $tblrow = [];
    $query = $this->database->select('advance_script_manager', 'a');
    $query->fields('a', [
      'id',
      'script_name',
      'visibility_section',
      'created',
      'updated',
      'status',
    ]);
    if (!empty($visibility) && $visibility != '') {
      $query->condition('visibility_section', $visibility);
    }
    if (!empty($status) && $status != '') {
      $query->condition('status', $status);
    }
    $query->orderBy('id', 'DESC');
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(20);
    $result = $pager->execute()->fetchAll();

    foreach ($result as $row) {
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

    return $tblrow;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    $item_ids = $form_state->getValue('list_scripts');
    $res = $this->database->delete('advance_script_manager')
      ->condition('id', $item_ids, 'IN')
      ->execute();
    if ($res) {
      $this->messenger->addMessage($this->t('Scripts has been deleted.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deactivateSelected(array &$form, FormStateInterface $form_state) {
    // Display result.
    $item_ids = $form_state->getValue('list_scripts');
    if (!empty($item_ids)) {
      $res = $this->database->update('advance_script_manager')
        ->fields(['status' => 2])
        ->condition('id', $item_ids, 'IN')
        ->execute();
      if ($res) {
        $this->messenger->addMessage($this->t('Scripts has been deactivated.'));
      }
    }
  }

}
