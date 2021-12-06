<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die();



if (! class_exists('PhocaDownloadCategory')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocadownload/libraries/phocadownload/category/category.php');
}

class JFormFieldPhocaDownloadCategoryModule extends JFormField
{
	protected $type 		= 'PhocaDownloadCategoryModule';

	protected function getInput() {

        $app = Factory::getApplication();
		// Initialize variables.
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		$db = JFactory::getDBO();

       //build the list of categories
		$query = 'SELECT a.title AS text, a.id AS value, a.parent_id as parentid'
		. ' FROM #__phocadownload_categories AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();

		// TODO - check for other views than category edit
		$view 	= $app->input->get( 'view' );
		$catId	= -1;
		if ($view == 'phocadownloadcat') {
			$id 	= $this->form->getValue('id'); // id of current category
			if ((int)$id > 0) {
				$catId = $id;
			}
		}
		/*if ($view == 'phocadownloadfile') {
			$id 	= $this->form->getValue('catid'); // id of current category
			if ((int)$id > 0) {
				$catId = $id;
			}
		}*/



		$required	= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;

		$tree = array();
		$text = '';
		$tree = PhocaDownloadCategory::CategoryTreeOption($data, $tree, 0, $text, $catId);

		//if ($required == TRUE) {

		//} else {

			//array_unshift($tree, JHTML::_('select.option', '', '- '.JText::_('COM_PHOCADOWNLOAD_SELECT_CATEGORY').' -', 'value', 'text'));
		//}
		return JHTML::_('select.genericlist',  $tree,  $this->name, $attr, 'value', 'text', $this->value, $this->id );
	}
}
?>
