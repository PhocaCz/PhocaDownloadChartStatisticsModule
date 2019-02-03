<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

// Include Phoca Download
if (!JComponentHelper::isEnabled('com_phocadownload', true)) {
    echo '<div class="alert alert-danger">Phoca Download Error: Phoca Download component is not installed or not published on your system</div>';
    return;
}

if (! class_exists('PhocaDownloadLoader')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocadownload/libraries/loader.php');
}
phocadownloadimport('phocadownload.access.access');

$user 		= JFactory::getUser();
$userLevels	= implode (',', $user->getAuthorisedViewLevels());
$aid 		= $user->get('aid', 0);
$db 		= JFactory::getDBO();
$app 		= JFactory::getApplication();
$menu 		= $app->getMenu();
$document	= JFactory::getDocument();
$mid		= $module->id; //additional Modul-ID

// PARAMS
$chart_width 		= $params->get( 'chart_width' );
$chart_height 		= $params->get( 'chart_height', 100 );
$number_item	 	= $params->get( 'number_item','' );
$displayCat		 	= $params->get( 'category_id','' );
//$display_per	 	= $params->get( 'display_per','' );
$chart_type	 		= $params->get( 'chart_type','PieChart' );
$chart_title		= $params->get( 'chart_title', '');
$chart_3d			= $params->get( 'chart_3d', 0);
$chart_piehole		= $params->get( 'chart_piehole', 0);
$chart_legend		= $params->get( 'chart_legend', 1);
$chart_column_yearly		= $params->get( 'chart_column_yearly', 1);
$chart_column_last_years	= $params->get( 'chart_column_last_years', 5);
$chart_column_stacked		= $params->get( 'chart_column_stacked', 0);
$chart_animation			= $params->get( 'chart_animation', 1);

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
$jnow		= JFactory::getDate();
$now		= $jnow->toSql();
$nullDate	= $db->getNullDate();
$wheres[]	= ' ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )';
$wheres[]	= ' ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )';
// SQL, QUERY
if (is_array($displayCat)) {
    ArrayHelper::toInteger($displayCat);
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

//yearly Column Chart
	$years='';
	$where_last_years = ((int)$chart_column_last_years != 0)?' WHERE YEAR(cu.date) > (YEAR(NOW())-'.(int)$chart_column_last_years.')':'';

	if($chart_column_yearly == 1 && ($chart_type == 'ColumnChart' or $chart_type == 'BarChart' or $chart_type == 'AreaChart' or $chart_type == 'LineChart')){

		// load all years (e.g. array(2015,2016,2017,2018))
		$qry_years = "SELECT YEAR(cu.date) AS 'year'"
				. " FROM #__phocadownload_user_stat as cu"
				. " LEFT JOIN( "
				. $query
				. ") as ch"
				. " ON cu.fileid = ch.id"
				. $where_last_years
				. " GROUP BY YEAR(cu.date)";
		$db->setQuery( $qry_years );
		$years = $db->loadObjectList();

		// load yearly Data
		$qry_yearly_items = "SELECT ch.id ";
			foreach ($years as $v) {
				$qry_yearly_items .= ", SUM(CASE WHEN Year(date) = ".$v->year." THEN count ELSE 0 END) AS 'v".$v->year."'";
			}
		$qry_yearly_items .= " FROM #__phocadownload_user_stat as cu"
				. " RIGHT JOIN( "
				. $query
				. ") as ch"
				. " ON cu.fileid = ch.id"
				. " GROUP BY ch.id"
				. " ORDER by ch.id";
		$db->setQuery( $qry_yearly_items );
		$yearly_items = $db->loadObjectList();

		$style_padding = "padding-bottom: 1em;";
	}


$styleO = '';
$styleC = '';
if (!empty($items)) {

		$styleO .= 'background-color: #ffffff;';
		$styleO .= 'text-align: center;';
		$styleO .= 'width: 100%;';
		$styleO .= (!empty($style_padding))?$style_padding:'';

		$styleO = 'style="'.$styleO.'"';

		$styleC .= ($chart_width != '')?'width: '.(int)$chart_width.'px;':'width: 100%;';

	if ($chart_height != '') {
		$styleC .= 'height: '.(int)$chart_height . 'px;';
	}
		$styleC = 'style="'.$styleC.'"';


	if ($chart_type == 'Gauge') {
		$package = 'gauge';
	} else {
		$package = 'corechart';
	}
	$s	 = array();
	$s[] = 'google.charts.load("current", {packages: ["'.$package.'"]});';
	$s[] = 'google.charts.setOnLoadCallback(drawChart_'.$mid.');';
	$s[] = 'function drawChart_'.$mid.'() {';
	$s[] = '  var data = new google.visualization.DataTable();';
	$s[] = '  data.addColumn("string", "'.JText::_('MOD_PHOCADOWNLOAD_CHART_STATISTICS_FILE').'");';

	//yearly ColumnChart
	if (!empty($years)) {
		foreach ($years as $v) {
			$s[] = '  data.addColumn("number", "'.$v->year.'");';
		}
	}
	else{
		$s[] = '  data.addColumn("number", "'.JText::_('MOD_PHOCADOWNLOAD_CHART_STATISTICS_DOWNLOADS').'");';
	}

	$s[] = '  data.addRows([';
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
			if (!empty($years)) {
				$a = "  ['".htmlspecialchars($value->title)."'";
				//yearly data
				foreach($yearly_items as $v) {
					if ($v->id == $value->id){
							foreach ($years as $y) {
									$year = 'v'.$y->year;
								$a .= ",".$v->$year."";
							}
						$a .= "],";
					}
				}
				$s[] = $a;
			}
			else{
				//all data
				$s[] = '  [\''.htmlspecialchars($value->title).'\', '.(int)$value->hits.'],';
			}
		}
	}

	$s[] = ' ]);';
	$s[] = ' ';
	$s[] = '  var options = {';
	if ($chart_title != '') {
		$s[] = "  'title':'".htmlspecialchars($chart_title)."',";
	}
		$s[] = "  hAxis: {title: 'Files', titleTextStyle: {color: 'grey'}},";

	if ($chart_animation == 1) {
	    $s[] = "  animation: {'startup': true, duration: 1000, easing: 'in'},";
	}

	if ($chart_type == 'PieChart'){
		if ($chart_3d == 1) {
			$s[] = "  'is3D': true,";
		}
		if ((float)$chart_piehole > 0 && (float)$chart_piehole <= 1) {
			$s[] = '  pieHole: '.$chart_piehole.',';
		}
	}

	if ($chart_legend == 0) {
		$s[] = "  legend: 'none',";
	}
	if ($chart_column_stacked == 1) {
		$s[] = "  'isStacked':true,";
	}

		//$s[] = "  'width':".(int)$chart_width.",";
		//$s[] = "  'height':".(int)$chart_height."";

	$s[] = ' };';


	$s[] = ' ';
	$s[] = ' var chart = new google.visualization.'.htmlspecialchars($chart_type).'(document.getElementById(\'pdchsmo_chart_'.$mid.'\'));';
	$s[] = ' chart.draw(data, options);';

	//toggle Data Table Button
	$toggleScript = "
			var toggleButton = document.getElementById('pdchsmo_btn_".$mid."');
			if (toggleButton) {
			
			 function orgChart() {
				var chart = new google.visualization.".htmlspecialchars($chart_type)."(document.getElementById('pdchsmo_chart_".$mid."'));
				options.hAxis.title= 'Files';
				chart.draw(data, options);
			}
			function newChart() {
				var transposedData = transposeDataTable(data);
				var transposeChart = new google.visualization.".htmlspecialchars($chart_type)."(document.getElementById('pdchsmo_chart_".$mid."'));
				options.hAxis.title= 'Years';
				transposeChart.draw(transposedData, options);
			}
			toggleButton.onclick = function () {
				if (toggleButton.getAttribute('data') == 'files'){
					newChart();
					toggleButton.setAttribute ('data','year');
				}
				else{
					orgChart();
					toggleButton.setAttribute ('data','files');
				}
			 }
			}";

	$s[] = $toggleScript;

	$s[] = '}';

	$document->addScript('https://www.gstatic.com/charts/loader.js');
	$document->addScript(JURI::base(true) . '/media/mod_phocadownload_chart_statistics/js/toggleDataTable.js');
	$document->addScriptDeclaration(implode("\n", $s));
}

require(JModuleHelper::getLayoutPath('mod_phocadownload_chart_statistics'));
?>
