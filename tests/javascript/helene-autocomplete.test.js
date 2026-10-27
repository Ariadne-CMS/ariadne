"use strict";
const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const vm = require("node:vm");

let completer;
const context = {
    templateList: ['example.custom.html'],
    ace: {require: () => ({addCompleter: value => { completer = value; }})}
};
vm.createContext(context);
vm.runInContext(fs.readFileSync(path.join(__dirname, '../../www/js/helene-autocomplete.js'), 'utf8'), context);

function complete(line, prefix, enabled = true) {
    let result;
    let calls = 0;
    const session = {getDocument: () => ({getLine: () => line})};
    completer.getCompletions({getBehavioursEnabled: () => enabled}, session,
        {row: 0, column: line.length}, prefix, (error, suggestions) => {
            assert.equal(error, null);
            calls++;
            result = suggestions;
        });
    assert.equal(calls, 1, 'Always complete the callback, including unknown modules');
    return Array.from(result);
}

for (const name of [
    'array_first', 'array_last', 'array_find', 'array_find_key', 'array_any', 'array_all',
    'array_is_list', 'array_key_first', 'str_contains', 'str_starts_with', 'str_ends_with',
    'json_validate', 'openssl_pkey_get_private', 'openssl_sign', 'set_time_limit',
    'preg_replace_callback', 'implode', 'strftime', 'mb_str_pad', 'hash_hmac',
    'ldGetRequestedHost', 'ldHeader', 'ldSetContent', 'ar', 'getdatacache', 'getSetting'
]) {
    test('function completion: ' + name, () => {
        assert.ok(complete(name, name).some(item => item.value === name + '()'));
    });
}

for (const name of ['extract', 'exec', 'eval', 'file_get_contents', 'unserialize', 'imap_open', 'allow']) {
    test('does not suggest unavailable function: ' + name, () => {
        assert.ok(!complete(name, name).some(item => item.value === name + '()'));
    });
}

test('function completion is case insensitive and preserves declaration spelling', () => {
    assert.ok(complete('LDGETREQ', 'LDGETREQ').some(item => item.value === 'ldGetRequestedHost()'));
});

test('ar:: completes methods without inserting ar:: twice', () => {
    const item = complete('ar::con', 'con').find(item => item.value === 'construct()');
    assert.equal(item.name, 'ar::construct()');
    assert.equal('ar::con'.slice(0, -3) + item.value, 'ar::construct()');
});

test('ar:: exposes wrapper methods and module shortcuts, not internal ar methods', () => {
    const values = complete('ar::', '').map(item => item.value);
    for (const value of ['callStatic()', 'unwrap()', 'html()', 'loader()', 'nls()']) assert.ok(values.includes(value));
    for (const value of ['autoload()', 'getObject()', 'context()']) assert.ok(!values.includes(value));
});

for (const [module, method] of [
    ['loader', 'inputStream'], ['loader/session', 'start'], ['nls', 'dict'],
    ['formats/csv', 'parse'], ['formats/markdown', 'compile'], ['html', 'zen'],
    ['html', 'attribute'], ['html/form', 'findField'], ['http/cookie', 'get'],
    ['tasks', 'add'], ['connect/multigate', 'client']
]) {
    test('module method: ' + module + '->' + method, () => {
        const line = "ar('" + module + "')->" + method;
        assert.ok(complete(line, method).some(item => item.value === method + '()'));
    });
}

test('ar::html()-> also completes module methods', () => {
    assert.ok(complete('ar::html()->ze', 'ze').some(item => item.value === 'zen()'));
});

test('unknown modules and prototype names return no completions', () => {
    for (const module of ['unknown', 'toString', '__proto__']) {
        assert.deepEqual(complete("ar('" + module + "')->a", 'a'), []);
    }
});

test('restricted module methods stay out of suggestions', () => {
    assert.ok(!complete("ar('pinp')->", '').some(item => item.value === 'allow()'));
    assert.ok(!complete("ar('events')->", '').some(item => item.value === 'remove()'));
    assert.ok(!complete("ar('html')->", '').some(item => item.value === 'parseChildren()'));
});

test('module paths do not duplicate the already typed directory', () => {
    const line = "ar('formats/ma";
    const item = complete(line, 'ma').find(item => item.name === 'formats/markdown');
    assert.equal(line.slice(0, -2) + item.value, "ar('formats/markdown");
});

test('module completion accepts double quotes and whitespace', () => {
    assert.ok(complete('ar( "loader" )->inp', 'inp').some(item => item.value === 'inputStream()'));
});

test('template completion does not duplicate a dotted name', () => {
    const line = "ar::call('example.custom.ht";
    const item = complete(line, 'ht').find(item => item.name === 'example.custom.html');
    assert.equal(line.slice(0, -2) + item.value, "ar::call('example.custom.html");
});

test('keywords do not gain function parentheses', () => {
    assert.ok(complete('else', 'else').some(item => item.value === 'else'));
    assert.ok(!complete('else', 'else').some(item => item.value === 'else()'));
});

test('disabled behaviours and empty global prefixes return no results', () => {
    assert.deepEqual(complete('array_', 'array_', false), []);
    assert.deepEqual(complete('', ''), []);
});
