<?php

require_once 'primarycontact.civix.php';
use CRM_Primarycontact_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function primarycontact_civicrm_config(&$config) {
  _primarycontact_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function primarycontact_civicrm_xmlMenu(&$files) {
  _primarycontact_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function primarycontact_civicrm_install() {
  _primarycontact_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function primarycontact_civicrm_postInstall() {
  _primarycontact_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function primarycontact_civicrm_uninstall() {
  _primarycontact_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function primarycontact_civicrm_enable() {
  _primarycontact_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function primarycontact_civicrm_disable() {
  _primarycontact_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function primarycontact_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _primarycontact_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function primarycontact_civicrm_managed(&$entities) {
  _primarycontact_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function primarycontact_civicrm_caseTypes(&$caseTypes) {
  _primarycontact_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function primarycontact_civicrm_angularModules(&$angularModules) {
  _primarycontact_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function primarycontact_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _primarycontact_civix_civicrm_alterSettingsFolders($metaDataFolders);
}




// FIXME: preProcess hook doesn't work before CiviCRM 4.5
// TODO: test that it works now in CiviCRM 4.7+ -- is it still usefull ?
/*function maincontact_civicrm_preProcess($formName, &$form) {

  // for organization contribution form, initialize on behalf with main contact relationship instead of employee relationship
  // FIXME: for now, just assuming employer relationship is created and rely on this to get the org infos - cf. redmine:16449

  if ( is_a( $form, 'CRM_Contribute_Form_Contribution_OnBehalfOf') ) {

    $relationship_type_id = CRM_Primarycontact_Utils::getRelationshipTypeID();

    // on behalf profile is there
    if ($relationship_type_id && $form->_profileId && CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', $form->_profileId, 'is_active')) {

      // get permissionned main contact relationship
      $contactID = $form->_contactID;
      $form->_employers = CRM_Contact_BAO_Relationship::getPermissionedContacts($contactID, $relationship_type_id);

      if (count($form->_employers) == 1) {
        foreach ($form->_employers as $id => $value) {
          $form->_organizationName = $value['name'];
          $orgId = $id;
        }
        $form->assign('orgId', $orgId);
        $form->assign('organizationName', $form->_organizationName);
      }

    }
  }

}*/




/**
 * Implements hook_civicrm_post().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */
function primarycontact_civicrm_post($op, $objectName, $id, &$params) {

  // ensure that we have only one primary contact by deactivating previous ones
  // for new primary contact relationship, add proper permissions
  if ($objectName == 'Relationship' && ($op == 'create' || $op == 'edit')) {
    //watchdog('debug', print_r($params,1));

    $relationship_type_id = CRM_Primarycontact_Utils::getRelationshipTypeID();

    // primary contact relationship ?
    //watchdog('debug', var_export( _maincontact_is_current($params), 1));
    if ($relationship_type_id && $params->relationship_type_id == $relationship_type_id && CRM_Primarycontact_Utils::isCurrentRelationship($id, $params)) {

      // find all others active maincontact relationships for this org and disable them
      $p = array(
        'filters' => array('is_current' => 1),
        'relationship_type_id' => $relationship_type_id,
        'contact_id_b' => $params->contact_id_b,
        'id' => array('NOT IN' => array($params->id)),
      );

      $result = civicrm_api3('relationship', 'get', $p);
//      watchdog('debug', '(maincontact_civicrm_post) relationships -- ' . print_r($result, 1));
      foreach ($result['values'] as $relid => $rel) {
        $rel['is_active'] = 0;
        civicrm_api3('relationship', 'create', $rel);

        // use BAO to ensure inherited membership are disable
        CRM_Contact_BAO_Relationship::disableEnableRelationship($relid, CRM_Core_Action::DISABLE);
      }

    }

    // update the permissioned relationship so that the individual would be able to have a prefilled renewal membership form for the org
    if ($params->relationship_type_id == $relationship_type_id) {

      // TODO: see if we still need to add permission on Employee instead of the Primary contact relationship
      //$relTypeId = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', 'Employee of', 'id', 'name_a_b');
      $relTypeId = $relationship_type_id;

      // ensure that the organization membership is current
      $perm = CRM_Primarycontact_Utils::isCurrentRelationship($params->id, $params) ? 1 : 0;

      // FIXME: this should go in CRM_Primarycontact_Utils ?
      $sql = "UPDATE civicrm_relationship SET is_permission_a_b = %1 WHERE contact_id_a = %2 AND contact_id_b = %3 AND relationship_type_id = %4";
      $sqlp = array(
        1 => array($perm, 'Integer'),
        2 => array($params->contact_id_a, 'Integer'),
        3 => array($params->contact_id_b, 'Integer'),
        4 => array($relTypeId, 'Integer'),
      );
      //watchdog('debug', 'employer upd -- '.  $sql . ' -- ' . print_r($sqlp, 1));
      CRM_Core_DAO::executeQuery($sql, $sqlp);
    }
  }

}


/**
 * Implements hook_civicrm_postProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postProcess
 */
function primarycontact_civicrm_postProcess($formName, &$form) {

  // for organization membership form, create main contact relationship with proper permissions

  if (is_a($form, 'CRM_Contribute_Form_Contribution_Confirm')) {

    $submit = $form->getVar('_submitValues');
    $relationship_type_id = CRM_Primarycontact_Utils::getRelationshipTypeID();

    if ($relationship_type_id && !empty($submit['onbehalf']['organization_name'])) {
      $orgName = $submit['onbehalf']['organization_name'];
      $contactID = $form->get('contactID');

      // get contact type
      $type = civicrm_api3('Contact', 'getvalue', array('return' => 'contact_type', 'contact_id' => $contactID));

      // get or create employer from name
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'contact_type' => 'Organization',
        'organization_name' => $orgName,
      );
      $result = civicrm_api('Contact', 'get', $params);

      if ($result['count'] == 1) {
        $org = $result['values'][0];
      } else if ($result['count'] == 0) {
        // failsafe error : should have been created
        Civi::log()->debug('primarycontact_civicrm_postProcess error : no organization found');
      } else {
        // failsafe error : ambiguous employer
        Civi::log()->debug('primarycontact_civicrm_postProcess error : ambiguous organization');
      }

      // create primary contact relationship
      if (isset($org)) {
        $params = array(
          'version' => 3,
          'sequential' => 1,
          'relationship_type_id' => $relationship_type_id,
          'contact_id_a' => $contactID,
          'contact_id_b' => $org['contact_id'],
          'is_permission_b_a' => 1,
          'is_permission_a_b' => 1,
        );

        $result = civicrm_api('Relationship', 'create', $params);
        //watchdog('debug', 'res -- ' . print_r($result, 1));
      }

    }
  }

}


/**
 * Implements hook_civicrm_tokens().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tokens
 */
function primarycontact_civicrm_tokens( &$tokens ) {
  $relationship_type_id = CRM_Primarycontact_Utils::getRelationshipTypeID();
  if ($relationship_type_id) {
    $tokens['primarycontact'] = array(
      'primarycontact.renew' => E::ts("Primary Contact: renew link"),
    );
  }
}


/**
 * Implements hook_civicrm_tokenValues().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_tokens
 */
function primarycontact_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null) {

  $relationship_type_id = CRM_Primarycontact_Utils::getRelationshipTypeID();
  if ($relationship_type_id) {

    $contacts = implode(',', $cids);
    $tokens += array(
      'primarycontact' => array(),
    );

    // defaults
    $targetcid = array();
    foreach ($cids as $cid) {
      $targetcid[$cid] = $cid;
    }

    // if organization, get primary contact otherwise keep the individual contact
    $sql = "
SELECT r.contact_id_b as cid, r.contact_id_a as main_contact
FROM civicrm_relationship r
WHERE r.relationship_type_id = %1
AND r.contact_id_b IN ($contacts)";

    //watchdog('debug', 'sql -- $sql);
    $dao = &CRM_Core_DAO::executeQuery(
      $sql,
      array(1 => array('Integer', $relationship_type_id))
    );
    while ($dao->fetch()) {
      $cid = $dao->cid;
      $targetcid[$cid] = $dao->main_contact;
    }

    foreach ($targetcid as $cid => $tcid) {
      $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum($tcid);
      $url = CRM_Utils_System::url('civicrm/contribute/transact', "reset=1&id=2&cid={$tcid}&cs={$cs}", TRUE);
      $values[$cid]['link.renew'] = $url;
    }
  }

}





