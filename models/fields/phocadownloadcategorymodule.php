<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (! class_exists('PhocaDownloadLoader')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocadownload/libraries/loader.php');
}
phocadownloadimport('phocadownload.category.category');

class JFormFieldPhocaDownloadCategoryModule extends JFormField
{
	protected $type 		= 'PhocaDownloadCategoryModule';

	protected function getInput() {
	
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
		$app = JFactory::getApplication();
		$view = $app->input->getCMD('view', '');
		
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