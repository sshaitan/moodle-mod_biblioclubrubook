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
 * @copyright 2022 ООО «НексМедиа»
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import Vue from 'vue';
import BookPickerModalBody from './book_picker_modal_body';

export default class BookPickerModal {

    constructor(parentVue) {
        this.parentVue = parentVue;
    }

    async show(onHideCallback) {
        this.modal = await ModalFactory.create({
            type: ModalFactory.types.CANCEL,
            title: this.parentVue.strings.modal_title,
            body: '',
        });

        this.modal.setLarge();

        this.modal.getRoot().on(ModalEvents.hidden, function() {
            this.vue.$destroy();
            this.modal.setBody('');
            onHideCallback();
        }.bind(this));

        this.modal.getRoot().on(ModalEvents.shown, function() {
            const template = '<div id="book-picker-modal-body"><book-picker-modal-body @book-selected="onBookSelected"></book-picker-modal-body></div>';
            this.modal.setBody(template);

            this.vue = new Vue({
                el: '#book-picker-modal-body',
                name: 'BookPickerModalWrapper',
                components: {
                    BookPickerModalBody,
                },
                methods: {
                    onBookSelected: function () {
                        this.modal.hide();
                    }.bind(this),
                },
                parent: this.parentVue,
            });
        }.bind(this));

        this.modal.show();

    }

    hide() {
        this.modal.hide();
    }
}
