<?php

namespace Drupal\user_profile\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for User Profile entities.
 */
class UserProfileViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['user_profile']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('User Profile'),
      'help' => $this->t('The User Profile ID.'),
    );

    return $data;
  }

}
