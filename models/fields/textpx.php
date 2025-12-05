<?php
/*
# Author    it-conserv.de
# Copyright (C) 2017 it-conserv.de All Rights Reserved.
# License - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: it-conserv.de
*/

// Check to ensure this file is included in Joomla!
use Joomla\CMS\Form\FormField;

defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.formfield');

/* *******************
 Example in the xml-file
 copy this file in the folder elements:
 <fields name="params" addfieldpath="/modules/mod_j51inlineicons/elements">
 <field name="myfield" type="textpx" default="42" label="Size" description="Size in px" />
 ********************* */

class JFormFieldtextpx extends FormField {

        protected $type = 'textpx';

		protected function getLabel(){

			return parent::getLabel();
		}

        public function getInput() {

            return 	'<div class="input-append">'.
					'<input class="input-medium form-control" style="width: 10em;display:inline;margin-right: 1em;" type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
					. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>'.
					'<span class="add-on">px</span>'.
					'</div>';
        }
}
