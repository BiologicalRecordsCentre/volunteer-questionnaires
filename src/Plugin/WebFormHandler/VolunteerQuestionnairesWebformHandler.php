<?php

namespace Drupal\volunteer_questionnaires\Plugin\WebformHandler;

use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\user\Entity\User;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Class VolunteerQuestionnairesWebformHandler.
 *
 * @WebformHandler(
 *   id = "volunteer_questionnaires",
 *   label = @Translation("Volunteer Questionnaires"),
 *   description = @Translation("Webform handler integrating with Volunteer Questionnaires."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 * )
 */
class VolunteerQuestionnairesWebformHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   *
   * Post save of a form, need to remove it from the list of requested forms.
   */
  public function postSave(WebformSubmissionInterface $webformSubmission, $update = TRUE) {
    // Remove the form from the list of requested forms.
    $account = \Drupal::currentUser();
    if ($account->id()) {
      $user = User::load($account->id());
      $forms = $user->get('field_requested_questionnaires')->referencedEntities();
      $formsToSave = [];
      $thisFormId = $webformSubmission->getWebform()->id();
      foreach ($forms as $form) {
        if ($form->id() !== $thisFormId) {
          // If form isn't the submitted one, keep in the list to save.
          $formsToSave[] = ['target_id' => $form->id()];
        }
      }
    }
    // Update the field and save.
    $user->get('field_requested_questionnaires')->setValue($formsToSave);
    $user->save();
  }

}
