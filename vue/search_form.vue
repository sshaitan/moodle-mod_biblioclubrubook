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
    <form v-on:submit.prevent="search">
        <div class="input-group mb-3">
            <input type="text" class="form-control" v-model="queryString" @input="onInput"/>
            <button class="btn btn-secondary" type="submit" v-text="strings.search_btn"></button>
        </div>
    </form>
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        name: 'SearchForm',
        data: () => ({
            queryString: '',
        }),
        computed: {
            ...mapState([
                'strings',
            ]),
        },
        created: function () {
            this.queryString = this.$store.state.queryString;
        },
        methods: {
            onInput: function (event) {
                this.$emit('input', event.target.value);
            },
            search: function () {
                this.$store.dispatch('search', this.queryString);
            },
        },
    };
</script>
