<?php

/**
 * Class CRM_Primarycontact_Utils
 */
class CRM_Primarycontact_Utils {

  /**
   * Get the primary contact relationship type ID
   *
   * @return int RelationshipTypeID
   */
  public static function getRelationshipTypeID() {

    // FIXME: we might want to cache it
    $result = civicrm_api3('Setting', 'get', array(
      'sequential' => 1,
      'return' => array('primarycontact_relationship_type_id'),
    ));
    if (!empty($result['values'][0]['primarycontact_relationship_type_id'])) {
      return $result['values'][0]['primarycontact_relationship_type_id'];
    }
  }


  /**
   * Get wether the given relationship is current
   *
   * @return bool
   */
  public static function isCurrentRelationship($id, $data) {
    // FIXME : won't work if end_date is set in the future but not really used in this case
    return $data->is_active == 1 && (!isset($data->end_data) || empty($data->end_date) || is_null($data->end_date));
  }


  public static function getPrimaryContact($org_id) {
    $relationship_type_id = CRM_Primarycontact_Utils::getRelationshipTypeID();

    $dao = &CRM_Core_DAO::executeQuery("
SELECT r.contact_id_a as primary_id
FROM civicrm_relationship r
  INNER JOIN civicrm_contact c ON c.id = r.contact_id_a
  INNER JOIN civicrm_contact org ON org.id = r.contact_id_b
WHERE r.relationship_type_id = %1
  AND r.contact_id_b = %2
  AND r.is_active = 1",
      array(
        1 => array($relationship_type_id, 'Integer'),
        2 => array($org_id, 'Integer'),
      )
    );
    if ($dao->fetch()) {
      return $dao->primary_id;
    }
    return FALSE;

  }

}
