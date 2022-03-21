<?php

namespace Drupal\volunteer_questionnaires\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;

/**
 * Config form for the volunteer_questionnaires module.
 */
class VolunteerQuestionnairesSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'volunteer_questionnaires_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form constructor.
    $form = parent::buildForm($form, $form_state);
    // Default settings.
    $config = $this->config('volunteer_questionnaires.settings');
    $entities = \Drupal::entityTypeManager()->getStorage('webform')->loadMultiple(NULL);
    $options = [
      '' => $this->t('- None -'),
    ];
    foreach ($entities as $id => $webform) {
      $options[$id] = $webform->get('title');
    }
    $roles = Role::loadMultiple();
    $roleOptions = [
      '' => $this->t('- None -'),
    ];
    foreach ($roles as $role) {
      // Create list of all user roles - anonymous doesn't count as can't be
      // for a real user.
      if ($role->id() !== 'anonymous') {
        $roleOptions[$role->id()] = $role->get('label');
      }
    }

    $form['signup_webform_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Post sign-up questionnaire'),
      '#default_value' => $config->get('signup_webform_id'),
      '#description' => $this->t('Select the webform to request that a user fills in if they opt-in on sign-up.'),
      '#options' => $options,
    ];
    $form['request_form_from_role'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Request questionnaire completion by a group of users'),
    ];
    $form['request_form_from_role']['requested_form'] = [
      '#type' => 'select',
      '#title' => $this->t('Questionnaire to request'),
      '#description' => $this->t('Select the Webform questionnaire to request.'),
      '#options' => $options,
    ];
    $form['request_form_from_role']['requested_role'] = [
      '#type' => 'select',
      '#title' => $this->t('Role to request the form is completed by'),
      '#description' => $this->t('Members of this role will have the form added to their list of requested forms.'),
      '#options' => $roleOptions,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Reject state where only one of requested_form and requested_role are set.
    if (empty($form_state->getValue('requested_form')) !== empty($form_state->getValue('requested_role'))) {
      $form_state->setErrorByName('requested_form', $this->t('Select both the form to request and the role to request it for.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('volunteer_questionnaires.settings');
    $config->set('signup_webform_id', $form_state->getValue('signup_webform_id'));
    $config->save();
    // If the requested_form and requested_role field set, need to find all
    // opted-in users with that role and request the questionnaire is
    // completed by them.
    if ($form_state->getValue('requested_form') && $form_state->getValue('requested_role')) {
      $query = \Drupal::entityTypeManager()->getStorage('user')
        ->getQuery()
        // Not blocked.
        ->condition('status', 1)
        // Opted-in.
        ->condition('field_questionnaire_opt_in', 1);
      // Authenticated role doesn't need a filter.
      if ($form_state->getValue('requested_role') !== 'authenticated') {
        $query->condition('roles', $form_state->getValue('requested_role'));
      }
      $ids = $query->execute();
      $users = User::loadMultiple($ids);
      $usersUpdated = 0;
      foreach ($users as $user) {
        $requestedQuestionnaires = $user->get('field_requested_questionnaires')->referencedEntities();
        $alreadyGotQuestionnaire = FALSE;
        // Track the existing questionnaires in the correct format for saving
        // the field, in case we need to do an update.
        $fieldDataToSave = [];
        foreach ($requestedQuestionnaires as $questionnaire) {
          $fieldDataToSave[] = ['target_id' => $questionnaire->id()];
          if ($questionnaire->id() === $form_state->getValue('requested_form')) {
            $alreadyGotQuestionnaire = TRUE;
          }
        }
        // If the questionnaire not in this user's list of requested
        // questionnaires, then add it and save the account.
        if (!$alreadyGotQuestionnaire) {
          $fieldDataToSave[] = ['target_id' => $form_state->getValue('requested_form')];
          $user->get('field_requested_questionnaires')->setValue($fieldDataToSave);
          $user->save();
          $usersUpdated++;
        }
      }
      \Drupal::messenger()->addMessage($this->t('Number of users updated: %count', ['%count' => $usersUpdated]));
    }
    return parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'volunteer_questionnaires.settings',
    ];
  }

}
