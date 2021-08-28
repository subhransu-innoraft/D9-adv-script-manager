<?php

namespace Drupal\advance_script_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SearchscriptsForm.
 */
class SearchscriptsForm extends FormBase {

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
    return 'searchscripts_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $visibility = $this->requestStack->getCurrentRequest()->query->get('visibility');
    $status = $this->requestStack->getCurrentRequest()->query->get('status');

    $form['container'] = array(
      '#type' => 'fieldset',
      '#title' => t('Name'),
      '#collapsible' => TRUE, // Added
      '#collapsed' => FALSE,  // Added
    );

    $form['container']['visibility'] = [
      '#type' => 'select',
      '#title' => $this->t('Visibility'),
      '#options' => [
        '' => $this->t('--Select one--'),
        'Header' => $this->t('Header'),
        'Footer' => $this->t('Footer'),
        'Body' => $this->t('Body'),
        ],
      '#default_value' => (!empty($visibility)) ? $visibility : 'Any',
      '#size' => 1,
      '#weight' => '0',
    ];
    $form['container']['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Status'),
      '#options' => [
        '' => $this->t('--Select one--'),
        '1' => $this->t('Active'),
        '2' => $this->t('Disabled'),
      ],
      '#default_value' => (!empty($status)) ? $status : '',
      '#size' => 1,
      '#weight' => '0',
    ];
    $form['container']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];
    $form['container']['actions'] = [
      '#type' => 'actions',
    ];

    $form['container']['actions']['submit2'] = [
      '#type' => 'submit',
      '#value' => $this->t('Clear'),
      "#weight" => 2,
      '#button_type' => 'warning',
      '#submit' => [[$this, 'submitClearForm']],
      '#limit_validation_errors' => [],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $fields = $form_state->getValues();
    $visibility = $fields['visibility'];
    $status = $fields['status'];
    $url = Url::fromRoute('advance_script_manager.advance_script_controller_build')
      ->setRouteParameters([
        'visibility' => $visibility,
        'status' => $status,
      ]);
    $form_state->setRedirectUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function submitClearForm(array &$form, FormStateInterface $form_state) {
    $url = Url::fromRoute('advance_script_manager.advance_script_controller_build');
    $form_state->setRedirectUrl($url);
  }

}
