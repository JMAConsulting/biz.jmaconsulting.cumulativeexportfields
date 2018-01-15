<?php

require_once 'cumulativeexportfields.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function cumulativeexportfields_civicrm_config(&$config) {
  _cumulativeexportfields_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function cumulativeexportfields_civicrm_xmlMenu(&$files) {
  _cumulativeexportfields_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function cumulativeexportfields_civicrm_install() {
  civicrm_api3('CustomGroup', 'create', array(
    'title' => ts('Cumulative Fields'),
    'name' => 'cumulative_fields',
    'extends' => array(
      '0' => 'Contribution',
    ),
    'is_active' => 1,
  ));
  civicrm_api3('CustomField', 'create', array(
    'label' => ts('Current YTD'),
    'name' => 'calculated_ytd',
    'custom_group_id' => 'cumulative_fields',
    'data_type' => "String",
    'html_type' => "Text",
    'is_active' => 1,
    'is_view' => 1,
  ));
  civicrm_api3('CustomField', 'create', array(
    'label' => ts('Total Contributed Amount'),
    'name' => 'calculated_total',
    'custom_group_id' => 'cumulative_fields',
    'data_type' => "String",
    'html_type' => "Text",
    'is_active' => 1,
    'is_view' => 1,
  ));
  _cumulativeexportfields_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function cumulativeexportfields_civicrm_uninstall() {
  _cumulativeexportfields_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function cumulativeexportfields_civicrm_enable() {
  _cumulativeexportfields_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function cumulativeexportfields_civicrm_disable() {
  _cumulativeexportfields_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function cumulativeexportfields_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _cumulativeexportfields_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function cumulativeexportfields_civicrm_managed(&$entities) {
  _cumulativeexportfields_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function cumulativeexportfields_civicrm_caseTypes(&$caseTypes) {
  _cumulativeexportfields_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function cumulativeexportfields_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _cumulativeexportfields_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook_civicrm_pageRun
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pageRun
 */
function cumulativeexportfields_civicrm_pageRun(&$page) {
  // Hide view of cumulative fields on contribution view.
  if (get_class($page) == "CRM_Contribute_Page_Tab") {
    CRM_Core_Resources::singleton()->addScript("cj('#cumulative_fields__').hide();");
  }
}

/**
 * Implementation of hook_civicrm_export
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_export
 */
function cumulativeexportfields_civicrm_export(&$exportTempTable, &$headerRows, &$sqlColumns, &$exportMode) {
  // Only do this for contribution export.
  if ($exportMode == CRM_Export_Form_Select::CONTRIBUTE_EXPORT) {
    // Check if mandatory contribution ID field is present.
    if (empty($sqlColumns['contribution_id'])) {
      return;
    }

    // Check if either custom fields are selected.
    $ytd = civicrm_api3('CustomField', 'getvalue', array(
      'name' => 'calculated_ytd',
      'return' => 'id',
    ));
    $total = civicrm_api3('CustomField', 'getvalue', array(
      'name' => 'calculated_total',
      'return' => 'id',
    ));
    if (empty($sqlColumns['custom_' . $ytd]) || empty($sqlColumns['custom_' . $total])) {
      return;
    }

    $dao = CRM_Core_DAO::executeQuery("SELECT contribution_id FROM {$exportTempTable}");
    while ($dao->fetch()) {
      // Get contact ID from contribution.
      $cid = civicrm_api3('Contribution', 'getvalue', array(
        'id' => $dao->contribution_id,
        'return' => 'contact_id',
      ));

      // Retrieve annual amounts.
      list($count, $amount, $avg) = CRM_Contribute_BAO_Contribution::annual($cid);

      // Modify SQL.
      $sql = CRM_Core_DAO::executeQuery("UPDATE {$exportTempTable} SET custom_$ytd = '{$avg}', custom_$total = '{$amount}' WHERE contribution_id = {$dao->contribution_id}"); 
    }
  }
}