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
import moodleAjax from 'core/ajax';
import moodleStorage from 'core/localstorage';
import Notification from 'core/notification';

/**
 * Single ajax call to Moodle.
 */
async function ajax(method, args) {
    const request = {
        methodname: method,
        args: args,
    };

    try {
        return await moodleAjax.call([request])[0];
    } catch (e) {
        Notification.exception(e);
        throw e;
    }
}

export default {
    strict: process.env.NODE_ENV !== 'production',
    state: () => ({
        strings: {},
        selectedBook: {
            id: null,
            description: '',
            cover: '',
            biblio: ''
        },
        searching: false,
        queryString: '',
        searchError: false,
        pages: [],
        currentPageNum: null,
        pagesTotal: null,
    }),
    getters: {
        currentPage: state => {
            if (state.pages[state.currentPageNum] !== undefined) {
                return state.pages[state.currentPageNum];
            }
            return [];
        },
    },
    mutations: {
        setStrings(state, strings) {
            state.strings = strings;
        },
        setSelectedBook(state, publication) {
            state.selectedBook.id = publication.id;
            state.selectedBook.description = publication.hasOwnProperty('cname') ? publication.cname : publication.biblio_record;
            state.selectedBook.cover = publication.cover;
            state.selectedBook.biblio = publication.biblio;
        },
        setSearching(state, value) {
            state.searching = value;
        },
        setQueryString(state, value) {
            state.queryString = value;
        },
        setSearchError(state, value) {
            state.searchError = value;
        },
        unsetPages(state) {
            state.pages = [];
            state.pagesTotal = null;
            state.pagesLoaded = null;
        },
        setPage(state, payload) {
            Vue.set(state.pages, payload.page, payload.publications);
        },
        setCurrentPage(state, value) {
            state.currentPageNum = value;
        },
        setPagesTotal(state, value) {
            state.pagesTotal = value;
        },
    },
    actions: {
        async loadComponentStrings(context) {
            const lang = $('html').attr('lang').replace(/-/g, '_');
            const cacheKey = 'mod_biblioclubrubook/strings/' + lang;
            const cachedStrings = moodleStorage.get(cacheKey);
            if (cachedStrings) {
                context.commit('setStrings', JSON.parse(cachedStrings));
            } else {
                const request = {
                    methodname: 'core_get_component_strings',
                    args: {
                        'component': 'mod_biblioclubrubook',
                        lang,
                    },
                };
                const loadedStrings = await moodleAjax.call([request])[0];
                let strings = {};
                loadedStrings.forEach((s) => {
                    strings[s.stringid] = s.string;
                });
                context.commit('setStrings', strings);
                moodleStorage.set(cacheKey, JSON.stringify(strings));
            }
        },
        async search(context, queryString) {
            if (queryString == context.state.queryString) {
                return;
            }
            context.commit('unsetPages');
            context.commit('setQueryString', queryString);
            context.commit('setSearching', true);
            context.commit('setSearchError', false);
            try {
                const results = await ajax('mod_biblioclubrubook_search', {
                    searchquery: queryString,
                });
                let currentPage = results._meta.currentPage;
                context.commit('setPage', {
                    page: currentPage,
                    publications: results.publications,
                });
                context.commit('setCurrentPage', currentPage);
                context.commit('setPagesTotal', results._meta.pageCount);
                context.commit('setSearching', false);
                context.dispatch('preloadPage', currentPage + 1);
            } catch (e) {
                console.log(e);
                context.commit('setSearching', false);
                context.commit('setSearchError', true);
            }
        },
        async loadPage(context, page) {
            if (page > context.state.pagesTotal) {
                page = context.state.pagesTotal;
            }
            if (page < 1) {
                page = 1;
            }
            if (context.state.pages[page] !== undefined) {
                context.commit('setCurrentPage', page);
                context.dispatch('preloadPage', page - 1);
                context.dispatch('preloadPage', page + 1);
                return;
            }
            context.commit('setSearching', true);
            context.commit('setSearchError', false);
            try {
                const results = await ajax('mod_biblioclubrubook_search', {
                    searchquery: context.state.queryString,
                    page: page,
                });

                let currentPage = results._meta.currentPage;
                context.commit('setPage', {
                    page: currentPage,
                    publications: results.publications,
                });
                context.commit('setCurrentPage', currentPage);
                context.commit('setPagesTotal', results._meta.pageCount);
                context.commit('setSearching', false);
                context.dispatch('preloadPage', currentPage - 1);
                context.dispatch('preloadPage', currentPage + 1);
            } catch (e) {
                context.commit('setSearching', false);
                context.commit('setSearchError', true);
            }
        },
        async preloadPage(context, page) {
            if (page > context.state.pagesTotal) {
                page = context.state.pagesTotal;
            }
            if (page < 1) {
                page = 1;
            }
            if (context.state.pages[page] !== undefined) {
                return;
            }
            try {
                const results = await ajax('mod_biblioclubrubook_search', {
                    searchquery: context.state.queryString,
                    page: page,
                });
                page = results._meta.currentPage;
                context.commit('setPage', {
                    page: page,
                    publications: results.publications,
                });
            } catch (e) {

            }
        },
    },
};
