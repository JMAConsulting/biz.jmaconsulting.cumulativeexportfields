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
    'extends' => array(0 => 'Contribution'),
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
  $customNames = array(
    'calculated_ytd' => 'CustomField',
    'calculated_total' => 'CustomField',
    'cumulative_fields' => 'CustomGroup',
  );
  foreach ($customNames as $name => $entity) {
    civicrm_api3($entity, 'get', array(
      'name' => $name,
      "api.$entity.delete" => array(
        'id' => '$value.id',
      ),
    ));
  }
  _cumulativeexportfields_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function cumulativeexportfields_civicrm_enable() {
  civicrm_api3('CustomGroup', 'get', array(
    'name' => 'cumulative_fields',
    'api.CustomGroup.create' => array(
      'id' => '$value.id',
      'is_active' => 1,
    ),
  ));
  _cumulativeexportfields_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function cumulativeexportfields_civicrm_disable() {
  civicrm_api3('CustomGroup', 'get', array(
    'name' => 'cumulative_fields',
    'api.CustomGroup.create' => array(
      'id' => '$value.id',
      'is_active' => 0,
    ),
  ));
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

    $customFieldKeys = CRM_Core_BAO_Cache::getItem('cumulativeexportfields', 'custom field keys');

    if (empty($customFieldKeys)) {
      // Check if either custom fields are selected.
      foreach (array('calculated_ytd', 'calculated_total') as $name) {
        $customFieldKeys[] = 'custom_' . civicrm_api3('CustomField', 'getvalue', array(
          'name' => $name,
          'return' => 'id',
        ));
      }
      CRM_Core_BAO_Cache::setItem($customFieldKeys, 'cumulativeexportfields', 'custom field keys');
    }

    list($ytd, $total) = $customFieldKeys;
    if (empty($sqlColumns[$ytd]) || empty($sqlColumns[$total])) {
      return;
    }

    $result = CRM_Utils_SQL_Select::from('!tempTable')
            ->select(array('GROUP_CONCAT(contribution_id) as contri_ids', 'cc.contact_id'))
            ->join('cc', 'INNER JOIN civicrm_contribution cc ON cc.id = !tempTable.contribution_id ')
            ->groupBy('cc.contact_id')
            ->param('!tempTable', $exportTempTable)
            ->execute()
            ->fetchAll();

    $contactIDs = CRM_Utils_Array::collect('contact_id', $result);
    $records = _getAnnual($contactIDs);
    foreach ($result as $record) {
      if (!empty($record['contact_id'])) {
        $sql = sprintf("UPDATE %s SET %s = '%s', %s = '%s' WHERE contribution_id IN ( %s )",
          $exportTempTable,
          $ytd, $records[$record['contact_id']]['ytd'],
          $total, $records[$record['contact_id']]['total'],
          $record['contri_ids']
        );
        CRM_Core_DAO::executeQuery($sql);
      }
    }
  }
}


/**
 * @param array $contactIDs
 *
 * @return array
 */
function _getAnnual($contactIDs) {
  $contactIDs = implode(',', $contactIDs);

  $config = CRM_Core_Config::singleton();
  $startDate = $endDate = NULL;

  $currentMonth = date('m');
  $currentDay = date('d');
  if ((int ) $config->fiscalYearStart['M'] > $currentMonth ||
    ((int ) $config->fiscalYearStart['M'] == $currentMonth &&
      (int ) $config->fiscalYearStart['d'] > $currentDay
    )
  ) {
    $year = date('Y') - 1;
  }
  else {
    $year = date('Y');
  }
  $nextYear = $year + 1;

  if ($config->fiscalYearStart) {
    $newFiscalYearStart = $config->fiscalYearStart;
    if ($newFiscalYearStart['M'] < 10) {
      $newFiscalYearStart['M'] = '0' . $newFiscalYearStart['M'];
    }
    if ($newFiscalYearStart['d'] < 10) {
      $newFiscalYearStart['d'] = '0' . $newFiscalYearStart['d'];
    }
    $config->fiscalYearStart = $newFiscalYearStart;
    $monthDay = $config->fiscalYearStart['M'] . $config->fiscalYearStart['d'];
  }
  else {
    $monthDay = '0101';
  }
  $startDate = "$year$monthDay";
  $endDate = "$nextYear$monthDay";
  CRM_Financial_BAO_FinancialType::getAvailableFinancialTypes($financialTypes);
  $additionalWhere = " AND b.financial_type_id IN (0)";
  $liWhere = " AND i.financial_type_id IN (0)";
  if (!empty($financialTypes)) {
    $additionalWhere = " AND b.financial_type_id IN (" . implode(',', array_keys($financialTypes)) . ") AND i.id IS NULL";
    $liWhere = " AND i.financial_type_id NOT IN (" . implode(',', array_keys($financialTypes)) . ")";
  }
  $query = "
    SELECT b.contact_id as cid,
           count(*) as count,
           sum(total_amount) as ytd,
           (SELECT sum(total_amount) FROM civicrm_contribution WHERE contact_id = b.contact_id) as total,
           currency
      FROM civicrm_contribution b
      LEFT JOIN civicrm_line_item i ON i.contribution_id = b.id AND i.entity_table = 'civicrm_contribution' $liWhere
     WHERE b.contact_id IN ( $contactIDs )
       AND b.contribution_status_id = 1
       AND b.is_test = 0
       AND b.receive_date >= $startDate
       AND b.receive_date <  $endDate
    $additionalWhere
    GROUP BY b.contact_id, currency
    ";
  $dao = CRM_Core_DAO::executeQuery($query);
  $count = 0;
  $records = array();
  while ($dao->fetch()) {
    if ($dao->count > 0) {
      if (!empty($records[$dao->cid])) {
        $records[$dao->cid]['ytd'] .= ', ' . CRM_Utils_Money::format($dao->ytd, $dao->currency);
        $records[$dao->cid]['total'] .= ', ' . CRM_Utils_Money::format($dao->total, $dao->currency);
      }
      else {
        $records[$dao->cid] = array(
          'ytd' => CRM_Utils_Money::format($dao->ytd, $dao->currency),
          'total' => CRM_Utils_Money::format($dao->total, $dao->currency),
        );
      }
    }
    else {
      $records[$dao->cid] = array(
        'ytd' => '',
        'total' => '',
      );
    }
  }

  return $records;
}
