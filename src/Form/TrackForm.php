<?php

namespace Drupal\advance_script_manager\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TrackForm.
 */
class TrackForm extends ConfigFormBase {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->messenger = $container->get('messenger');
    $instance->database = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'advance_script_manager.track',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'track_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('advance_script_manager.track');
    $form['script_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Script name'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('script_name'),
    ];
    $form['visibility_settings'] = [
      '#type' => 'radios',
      '#title' => $this->t('Visibility settings'),
      '#options' => ['Header' => $this->t('Header'), 'Footer' => $this->t('Footer'), 'Body' => $this->t('Body')],
      '#default_value' => $config->get('visibility_settings'),
    ];
    $form['visibility'] = [
      '#type' => 'select',
      '#title' => $this->t('Visibility'),
      '#options' => ['Header' => $this->t('Header'), 'Footer' => $this->t('Footer'), 'Body' => $this->t('Body')],
      '#size' => 1,
      '#default_value' => $config->get('visibility'),
    ];
    $form['pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pages'),
      '#default_value' => $config->get('pages'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('advance_script_manager.track')
      ->set('script_name', $form_state->getValue('script_name'))
      ->set('visibility_settings', $form_state->getValue('visibility_settings'))
      ->set('visibility', $form_state->getValue('visibility'))
      ->set('pages', $form_state->getValue('pages'))
      ->save();
  }

}
