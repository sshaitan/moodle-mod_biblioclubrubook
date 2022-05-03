<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Book from biblioclub.ru module
 *
 * @package mod_biblioclubrubook
 * @copyright 2022 Pavel Lobanov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/biblioclubrubook/lib.php');

/**
 * Book from biblioclub.ru module
 *
 * @package mod_biblioclubrubook
 * @copyright 2022 Pavel Lobanov
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mod_biblioclubrubook_mod_form extends moodleform_mod {
    /**
     * Define this form - called by the parent constructor
     */
    public function definition() {
        global $CFG, $PAGE;
        $mform = $this->_form;
        $config = get_config('biblioclubrubook');

        // -------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size' => '48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $bookelements = array();
        $bookelements[] =& $mform->createElement('hidden', 'id', '', [
            'id' => 'id_book_id',
            'v-model' => 'selectedBook.id',
        ]);
        $bookelements[] =& $mform->createElement('hidden', 'biblio', '', [
            'id' => 'id_book_biblio',
            'v-model' => 'selectedBook.biblio',
        ]);
        $bookelements[] =& $mform->createElement('hidden', 'cover', '', [
            'id' => 'id_book_cover',
            'v-model' => 'selectedBook.cover',
        ]);
        $bookelements[] =& $mform->createElement('text', 'description', '', [
            'size' => '60',
            'readonly' => true,
            'v-model' => 'selectedBook.description',
        ]);
        $bookelements[] =& $mform->createElement(
            'button',
            'select',
            get_string('open_book_picker_btn', 'biblioclubrubook'),
            array(
                '@click' => 'showModal',
                'disabled' => true,
            )
        );
        $mform->addElement('group', 'book', get_string('mod_form_book', 'biblioclubrubook'), $bookelements, ' ', true);
        $mform->setType('book[id]', PARAM_INT);
        $mform->setType('book[description]', PARAM_RAW_TRIMMED);
        $mform->setType('book[biblio]', PARAM_RAW_TRIMMED);
        $mform->setType('book[cover]', PARAM_URL);
        $mform->addRule('book', null, 'required', null, 'client');

        $PAGE->requires->js_call_amd('mod_biblioclubrubook/bookpicker-lazy', 'init');

        $mform->addElement('text', 'page', get_string('mod_form_page', 'biblioclubrubook'), array('size' => '5'));
        $mform->setType('page', PARAM_RAW_TRIMMED);
	
	
        $mform->addElement('advcheckbox', 'showbibliography', get_string('mod_form_show_bibliography', 'biblioclubrubook'));
        $mform->setDefault('showbibliography', $config->showbibliography);

        $mform->addElement('advcheckbox', 'showcover', get_string('mod_form_show_cover', 'biblioclubrubook'));
        $mform->setDefault('showcover',0);
	    $mform->disabledIf('bibliographyposition', 'showbibliography', 'notchecked');
	
	
	    $this->standard_intro_elements();
        $element = $mform->getElement('introeditor');
        $attributes = $element->getAttributes();
        $attributes['rows'] = 5;
        $element->setAttributes($attributes);

        // -------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('appearance'));

        $bibliographypositions = array(
            BIBLIOCLUBRUBOOK_BIBLIOGRAPHY_POSITION_BEFORE => get_string('mod_form_bibliography_position_before', 'biblioclubrubook'),
            BIBLIOCLUBRUBOOK_BIBLIOGRAPHY_POSITION_AFTER => get_string('mod_form_bibliography_position_after', 'biblioclubrubook'),
        );
        $mform->addElement('select', 'bibliographyposition',
            get_string('mod_form_bibliography_position', 'biblioclubrubook'), $bibliographypositions);
        $mform->setType('bibliographyposition', PARAM_INT);
        $mform->setDefault('bibliographyposition', $config->bibliographyposition);
        $mform->disabledIf('bibliographyposition', 'showbibliography', 'notchecked');
		
        // -------------------------------------------------------
        $this->standard_coursemodule_elements();

        // -------------------------------------------------------
        $this->add_action_buttons();
    }

    /**
     * Allows the plugin to update the defaultvalues passed in to
     * the settings form (needed to set up draft areas for editor
     * and filemanager elements)
     * @param array $defaultvalues
     */
    public function data_preprocessing(&$defaultvalues) {
        if (!empty($defaultvalues['bookid'])) {
            $defaultvalues['book']['id'] = $defaultvalues['bookid'];
        }
        if (!empty($defaultvalues['bookdescription'])) {
            $defaultvalues['book']['description'] = $defaultvalues['bookdescription'];
        }
        if (!empty($defaultvalues['bookbiblio'])) {
            $defaultvalues['book']['biblio'] = $defaultvalues['bookbiblio'];
        }
        if (!empty($defaultvalues['bookcover'])) {
            $defaultvalues['book']['cover'] = $defaultvalues['bookcover'];
        }
        if (!empty($defaultvalues['bookpage'])) {
            $defaultvalues['page'] = $defaultvalues['bookpage'];
        }
    }

    /**
     * Perform minimal validation on the settings form
     * @param array $data
     * @param array $files
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['book']['id'])) {
            $errors['book'] = get_string('required');
            return $errors;
        }
        return $errors;
    }
}
