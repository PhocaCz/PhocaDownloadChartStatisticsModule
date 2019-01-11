<?php 
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
 // no direct access
defined('_JEXEC') or die('Restricted access');
echo '<div '.$styleO.'>';
	echo '<div id="pdchsmo_chart_'.$mid.'" '.$styleC.'></div>';
if($chart_column_yearly == 1 && ($chart_type == 'ColumnChart' or $chart_type == 'BarChart' or $chart_type == 'AreaChart' or $chart_type == 'LineChart')){
	echo '<button type="button" style="" class="btn" id="pdchsmo_btn_'.$mid.'" data="files">'.JText::_('MOD_PHOCADOWNLOAD_CHART_STATISTICS_TOGGLE_DATA').'</button>';
}		
	echo '</div>';
?>
