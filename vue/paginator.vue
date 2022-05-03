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
    <nav>
        <ul class="pagination justify-content-center">
            <template v-for="(type, page) in pages">
                <li v-if="type=='current'" class="page-item active">
                    <span class="page-link" v-text="page"></span>
                </li>
                <li v-if="type=='link'" class="page-item">
                    <a class="page-link" href="#" @click="loadPage(page)" v-text="page"></a>
                </li>
                <li v-if="type=='dots'" class="page-item disabled">
                    <span class="page-link" v-text="strings.paginator_dots"></span>
                </li>
                <li v-if="type=='prev'" class="page-item">
                    <a class="page-link" href="#" @click="prevPage()" v-text="strings.paginator_prev"></a>
                </li>
                <li v-if="type=='prev_disabled'" class="page-item disabled">
                    <span class="page-link" v-text="strings.paginator_prev"></span>
                </li>
                <li v-if="type=='next'" class="page-item">
                    <a class="page-link" href="#" @click="nextPage()" v-text="strings.paginator_next"></a>
                </li>
                <li v-if="type=='next_disabled'" class="page-item disabled">
                    <span class="page-link" v-text="strings.paginator_next"></span>
                </li>
            </template>
        </ul>
    </nav>
</template>

<script>
    import {mapState} from 'vuex';

    export default {
        name: 'Paginator',
        computed: {
            ...mapState([
                'strings',
                'currentPageNum',
                'pagesTotal',
            ]),
            pages() {
                if (!this.pagesTotal || !this.currentPageNum) {
                    return [];
                }
                let pages = [];
                let min = Math.max(2, Math.min(this.pagesTotal - 1, this.currentPageNum - 3));
                let max = Math.min(this.pagesTotal - 1, Math.max(2, this.currentPageNum + 3));
                if (this.currentPageNum > 1) {
                    pages[0] = 'prev';
                } else {
                    pages[0] = 'prev_disabled';

                }
                pages[1] = 'link';
                if (min > 3) {
                    pages[2] = 'dots';
                } else if (min == 3) {
                    pages[2] = 'link';
                }
                if (min <= max) {
                    for (let i = min; i <= max; i++) {
                        pages[i] = 'link';
                    }
                }
                if (max + 1 < this.pagesTotal - 1) {
                    pages[this.pagesTotal - 1] = 'dots';
                } else if (max + 1 == this.pagesTotal - 1) {
                    pages[this.pagesTotal - 1] = 'link';
                }
                pages[this.pagesTotal] = 'link';
                if (this.currentPageNum < this.pagesTotal) {
                    pages[this.pagesTotal + 1] = 'next';
                } else {
                    pages[this.pagesTotal + 1] = 'next_disabled';

                }
                pages[this.currentPageNum] = 'current';
                return pages;
            },
        },
        methods: {
            prevPage() {
                this.$store.dispatch('loadPage', this.currentPageNum - 1);
            },
            nextPage() {
                this.$store.dispatch('loadPage', this.currentPageNum + 1);
            },
            loadPage(number) {
                this.$store.dispatch('loadPage', number);
            },
        },
    };
</script>
