<?php

/**
 * Class CRM_Mosaico_Utils
 */
class CRM_Primarycontact_Utils {

  /**
   * Get the primary contact relationship type ID
   *
   * @return int RelationshipTypeID
   */
  public static function getRelationshipTypeID() {

    $result = civicrm_api3('Setting', 'get', array(
      'sequential' => 1,
      'return' => array('primary_contact_relationship_type_id'),
    ));
    if (!empty($result['values'][0]['primary_contact_relationship_type_id'])) {
      return $result['values'][0]['primary_contact_relationship_type_id'];
    }
  }

  public static function isCurrentRelationship($id, $data) {
    // FIXME : won't work if end_date is set in the future but not really used in this case
    return $data->is_active == 1 && (!isset($data->end_data) || empty($data->end_date) || is_null($data->end_date));
  }

}
