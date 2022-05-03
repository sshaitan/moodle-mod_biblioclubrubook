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
    <div class="books-list">
        <book-list-item
                v-for="publication in currentPage"
                :key="publication.id"
                :publication="publication"
                @book-selected="$emit('book-selected')"
        ></book-list-item>
    </div>
</template>

<script>
    import {mapGetters} from 'vuex';
    import BookListItem from './books_list_item';

    export default {
        name: "BooksList",
        components: {
            BookListItem,
        },
        computed: {
            ...mapGetters([
                'currentPage',
            ]),
        },
    }
</script>
