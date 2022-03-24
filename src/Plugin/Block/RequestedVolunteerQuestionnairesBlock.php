<?php

namespace Drupal\volunteer_questionnaires\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\user\Entity\User;

/**
 * Provides a 'Requested volunteer questionnaires' block.
 *
 * @Block(
 *   id = "requested_volunteer_questionnaires_block",
 *   admin_label = @Translation("Requested volunteer questionnaires block"),
 * )
 */
class RequestedVolunteerQuestionnairesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $list = [];
    $account = \Drupal::currentUser();
    if ($account->id()) {
      $user = User::load($account->id());
      if ($user->get('field_questionnaire_opt_in')->value) {
        $forms = $user->get('field_requested_questionnaires')->referencedEntities();
        foreach ($forms as $form) {
          $list[] = [
            'title' => $form->get('title'),
            'url' => $form->toUrl()->toString(),
          ];
        }
      }
    }
    return [
      '#theme' => 'requested_volunteer_questionnaires_block',
      '#items' => $list,
      '#simpletest' => 'bar',
    ];
  }

}
