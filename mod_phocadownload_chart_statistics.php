<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (! class_exists('PhocaDownloadLoader')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocadownload/libraries/loader.php');
}
phocadownloadimport('phocadownload.access.access');

$user 		= JFactory::getUser();
$userLevels	= implode (',', $user->getAuthorisedViewLevels());
$aid 		= $user->get('aid', 0);	
$db 		= JFactory::getDBO();
$menu 		= JSite::getMenu();
$document	= JFactory::getDocument();

// PARAMS 
$chart_width 		= $params->get( 'chart_width', 300 );
$chart_height 		= $params->get( 'chart_height', 100 );
$number_item	 	= $params->get( 'number_item','' );
$displayCat		 	= $params->get( 'category_id','' );
//$display_per	 	= $params->get( 'display_per','' );
$chart_type	 		= $params->get( 'chart_type','PieChart' );
$chart_title		= $params->get( 'chart_title', '');
$chart_3d			= $params->get( 'chart_3d', 0);
$chart_piehole		= $params->get( 'chart_piehole', 0);

// Items
$where		= array();	
$wheres[]	= ' a.textonly = 0';
$wheres[]	= '( (unaccessible_file = 1 ) OR (unaccessible_file = 0 AND a.access IN ('.$userLevels.') ) )';
$wheres[]	= '( (unaccessible_file = 1 ) OR (unaccessible_file = 0 AND cc.access IN ('.$userLevels.') ) )';
$wheres[]	= ' a.published = 1';
$wheres[]	= ' a.approved = 1';
$wheres[]	= ' cc.published = 1';
/*
if ($this->getState('filter.language')) {
	$wheres[] =  ' c.language IN ('.$db->Quote(JFactory::getLanguage()->getTag()).','.$db->Quote('*').')';
	$wheres[] =  ' cc.language IN ('.$db->Quote(JFactory::getLanguage()->getTag()).','.$db->Quote('*').')';
}*/

// Active
$jnow		=& JFactory::getDate();
$now		= $jnow->toSql();
$nullDate	= $db->getNullDate();
$wheres[]	= ' ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )';
$wheres[]	= ' ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )';
// SQL, QUERY
if (is_array($displayCat)) {
	JArrayHelper::toInteger($displayCat);
	$displayCatString	= implode(',', $displayCat);
	$wheres[]	= ' cc.id IN ( '.$displayCatString.' ) ';
} else if ((int)$displayCat > 0) {
	$wheres[]	= ' cc.id IN ( '.$displayCat.' ) ';
}
if ((int)$number_item > 0) {
	$limit	= ' LIMIT 0,'.(int)$number_item;
} else {
	$limit	= '';
}

$where		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );
$orderby	= ' ORDER by a.hits DESC';
$query = ' SELECT a.title, a.id, a.hits, cc.title AS categorytitle, cc.access as cataccess, cc.accessuserid as cataccessuserid '
	. ' FROM #__phocadownload AS a '
	. ' LEFT JOIN #__phocadownload_categories AS cc ON cc.id = a.catid '
	. $where
	. ' GROUP by a.id'
	. $orderby
	. $limit;
$db->setQuery( $query );
$items = $db->loadObjectList();


if (!empty($items)) {

	$styleO = '';
	if ($chart_width != '') {
		$styleO .= 'width: '.(int)$chart_width . 'px;';
	}
	if ($chart_height != '') {
		$styleO .= 'height: '.(int)$chart_height . 'px;';
	}
	$styleO = 'style="'.$styleO.'"';


	if ($chart_type == 'Gauge') {
		$package = 'gauge';
	} else {
		$package = 'corechart';
	}
	$s	 = array();
	$s[] = 'google.load("visualization", "1", {packages:["'.$package.'"]});';
	$s[] = 'google.setOnLoadCallback(drawChart);';
	$s[] = 'function drawChart() {';
	$s[] = '  var data = google.visualization.arrayToDataTable([';

	$s[] = '  [\''.JText::_('MOD_PHOCADOWNLOAD_CHART_STATISTICS_FILE').'\', \''.JText::_('MOD_PHOCADOWNLOAD_CHART_STATISTICS_DOWNLOADS').'\'],';
	$i = 0;
	foreach ($items as $value) {
		// USER RIGHT - Access of categories (if file is included in some not accessed category) - - - - -
		// ACCESS is handled in SQL query, ACCESS USER ID is handled here (specific users)
		$rightDisplay	= 0;
		if (!empty($value)) {
			$rightDisplay = PhocaDownloadAccess::getUserRight('accessuserid', $value->cataccessuserid, $value->cataccess, $user->getAuthorisedViewLevels(), $user->get('id', 0), 0);
		}
		// - - - - - - - - - - - - - - - - - - - - - -
		if ($rightDisplay == 1) {
			$s[] = '  [\''.htmlspecialchars($value->title).'\', '.(int)$value->hits.'],';
		}
	}

	$s[] = ' ]);';
	$s[] = ' ';
	$s[] = '  var options = {';
	if ($chart_title != '') {
		$s[] = '  title: \''.htmlspecialchars($chart_title).'\',';
	}
	if ($chart_3d == 1) {
		$s[] = '  is3D: true,';
	}

	if ((float)$chart_piehole > 0 && (float)$chart_piehole <= 1) {
		$s[] = '  pieHole: '.$chart_piehole.',';
	}
	$s[] = ' };';


	$s[] = ' ';
	$s[] = ' var chart = new google.visualization.'.htmlspecialchars($chart_type).'(document.getElementById(\'pdchsmo\'));';
	$s[] = ' chart.draw(data, options);';
	$s[] = '}';


	$document->addScript('https://www.google.com/jsapi');
	JFactory::getDocument()->addScriptDeclaration(implode("\n", $s));
}

require(JModuleHelper::getLayoutPath('mod_phocadownload_chart_statistics'));
?>