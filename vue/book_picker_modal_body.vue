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

<template>
    <div class="biblioclubrubook_book_picker_modal_body">
        <search-form></search-form>
        <div v-if="searchError" class="text-center text-danger" v-text="strings.search_error"></div>
        <div v-else-if="searching" class="text-center"  v-text="strings.searching"></div>
        <div v-else-if="pagesTotal > 0">
            <paginator></paginator>
            <books-list @book-selected="$emit('book-selected')"></books-list>
            <paginator></paginator>
        </div>
        <div v-else-if="queryString.length > 0" class="text-center" v-text="strings.nothing_found"></div>
    </div>
</template>

<script>
    import {mapState} from 'vuex';
    import SearchForm from './search_form';
    import BooksList from './books_list';
    import Paginator from './paginator';

    export default {
        name: 'BookPickerModalBody',
        components: {
            Paginator,
            BooksList,
            SearchForm,
        },
        computed: {
            ...mapState([
                'strings',
                'searching',
                'currentPageNum',
                'pagesTotal',
                'queryString',
                'searchError',
            ]),
        },
    };
</script>
