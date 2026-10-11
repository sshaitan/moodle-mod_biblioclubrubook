// Offline tests for modern and legacy Moodle modal creation. Run with node.
const assert = require('assert').strict;
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const source = fs.readFileSync(path.join(__dirname, '../../vue/book_picker_modal.js'), 'utf8')
    .replace(/^import .*;\r?$/gm, '')
    .replace('export default class', 'module.exports = class');

async function check(modern) {
    const calls = {modern: 0, legacy: 0, require: 0, shown: 0};
    const modal = {
        setLarge() {}, getRoot() { return {on() {}}; },
        show() { calls.shown++; },
    };
    const create = async options => {
        assert.equal(options.title, 'Pick a book');
        return modal;
    };
    const sandbox = {
        module: {exports: {}},
        ModalCancel: modern ? {create: options => { calls.modern++; return create(options); }} : {},
        ModalEvents: {hidden: 'hidden', shown: 'shown'},
        Vue: function() {},
        window: {
            require(dependencies, resolve) {
                calls.require++;
                assert.deepEqual(Array.from(dependencies), ['core/modal_factory']);
                resolve({types: {CANCEL: 'CANCEL'}, create: options => {
                    calls.legacy++;
                    assert.equal(options.type, 'CANCEL');
                    return create(options);
                }});
            },
        },
    };
    vm.runInNewContext(source, sandbox);
    const picker = new sandbox.module.exports({strings: {modal_title: 'Pick a book'}});
    await picker.show(() => {});
    assert.equal(calls.shown, 1);
    assert.equal(calls.modern, modern ? 1 : 0);
    assert.equal(calls.legacy, modern ? 0 : 1);
    assert.equal(calls.require, modern ? 0 : 1);
}

(async () => {
    await check(true);
    await check(false);
    for (const file of ['../../amd/build/bookpicker-lazy.min.js', '../../amd/src/bookpicker-lazy.js']) {
        const bundle = fs.readFileSync(path.join(__dirname, file), 'utf8');
        const dependencies = JSON.parse(bundle.match(/define\((\[[^\]]*\])/)[1]);
        assert(dependencies.includes('core/modal_cancel'));
        assert(!dependencies.includes('core/modal_factory'));
    }
    console.log('OK (2 modal compatibility cases; both AMD bundles checked)');
})().catch(error => { console.error(error); process.exitCode = 1; });
