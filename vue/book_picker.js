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

import $ from 'jquery';
import Vue from 'vue';
import Vuex from 'vuex';
import storeDefinition from './store';
import {mapState} from 'vuex';
import modal from './book_picker_modal';

export function init() {

    const bookIdSelector = '#id_book_id';
    const bookDescriptionSelector = '#id_book_description';
    const buttonSelector = '#id_book_select';
    const bookBiblio = '#id_book_biblio';
    const bookCover = '#id_book_cover';

    let appElement = $(bookIdSelector).closest('form').get(0);

    Vue.use(Vuex);

    new Vue({
        el: appElement,
        name: 'BookPickerInput',
        data: {},
        computed: {
            ...mapState([
                'strings',
                'selectedBook',
            ]),
            stringsLoaded: function () {
                return Object.keys(this.strings).length > 0;
            },
        },
        methods: {
            showModal: function () {
                $(buttonSelector).attr('disabled', true);
                this.modal = new modal(this);
                this.modal.show(function () {
                    $(buttonSelector).removeAttr('disabled');
                });
            },
            hideModal: function () {
                if (this.modal) {
                    this.modal.hide();
                }
            },
        },
        beforeMount: function() {
            let id = $(bookIdSelector).val();
            let biblio_record = $(bookDescriptionSelector).val();
            let biblio = $(bookBiblio).val();
            let cover = $(bookCover).val();
            this.$store.commit('setSelectedBook', {
                id,
                biblio_record,
                cover,
                biblio
            });
        },
        mounted: function () {
            this.$store.dispatch('loadComponentStrings');
        },
        watch: {
            'stringsLoaded': function () {
                $(buttonSelector).removeAttr('disabled');
            },
        },
        store: new Vuex.Store(storeDefinition),
    });
}