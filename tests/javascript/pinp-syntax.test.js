"use strict";

const assert = require("node:assert/strict");
const fs = require("node:fs");
const path = require("node:path");
const test = require("node:test");
const vm = require("node:vm");

// Run the shipped worker without Node globals, network access or a server.
const context = {postMessage() {}, setTimeout, clearTimeout};
context.self = context;
vm.createContext(context);
vm.runInContext(fs.readFileSync(path.join(__dirname, "../../www/js/ace/worker-php.js"), "utf8"), context);
const Worker = context.require("ace/mode/php_worker").PhpWorker;

function check(source, inline = false) {
    const events = [];
    Worker.prototype.onUpdate.call({
        inlinePhp: inline,
        doc: {getValue: () => source},
        sender: {emit: (event, annotations) => events.push({event, annotations})}
    });
    assert.equal(events.length, 1);
    return events[0];
}

function template(body) {
    return "<pinp>\r\n" + body.replace(/\r?\n/g, "\r\n") + "\r\n</pinp>";
}

for (const example of require("./pinp-syntax-cases.json")) {
    test("valid PINP: " + example.name, () => {
        const result = check(template(example.code));
        assert.equal(result.event, "ok", JSON.stringify(result.annotations));
    });
}

const mixed = {
    "HTML between code blocks": '<h1>Title</h1>\r\n<pinp> if ($show) { </pinp>text<pinp> } </pinp>',
    "alternative control syntax": '<pinp> if ($show): </pinp>text<pinp> endif; </pinp>',
    "uppercase opening tag": '<PINP> echo 1; </pinp>',
    "closing tag whitespace": '<pinp> echo 1; </ pinp >',
    "implicit semicolon": '<pinp> echo 1 </pinp>',
    "XML and literal PHP are template output": '<?xml version="1.0"?> <?php this is literal output ?>',
    "PHP output beside PINP": '<?php literal ?><pinp> echo 1; </pinp><?= literal ?>',
    "external compiler content": '<compile:less><pinp> not PHP </compile:less><pinp> echo 1; </pinp>',
    "tags in strings": template('echo "</pinp><pinp><?php"; echo \'</pinp>\';'),
    "tags in interpolated strings": template('echo "value: {$value}, </pinp>";'),
    "tags in comments": template('/* </pinp> */\n// </pinp> ?>\necho 1;'),
    "heredoc with tags": template('$value = <<<TEXT\n</pinp><pinp>\nTEXT;\necho $value;'),
    "empty template": '',
    "plain content": '<div>no code</div>',
    "HTML with quotes and comments": '<div title="<pinp> echo 1; </pinp>"><!-- <pinp> echo 2; </pinp> --></div>'
};
for (const [name, source] of Object.entries(mixed)) {
    test("template handling: " + name, () => {
        const result = check(source);
        assert.equal(result.event, "ok", JSON.stringify(result.annotations));
    });
}

for (const code of [
    '$a = ;', 'echo (1 + );', 'if ($a) {', '$a = "unterminated;',
    'echo [1, 2;', 'strlen(...["a"], "b");', 'strlen(string: "a", ...$args);'
]) {
    test("invalid syntax: " + code, () => {
        const result = check(template(code));
        assert.equal(result.event, "error");
        assert.equal(result.annotations[0].type, "error");
        assert.ok(Number.isInteger(result.annotations[0].row));
        assert.ok(Number.isInteger(result.annotations[0].column));
        assert.match(result.annotations[0].text, /./);
    });
}

test("annotations preserve original CRLF rows and columns", () => {
    const result = check('<h1>Title</h1>\r\n<pinp>\r\n\t$a = ;\r\n</pinp>');
    assert.equal(result.event, "error");
    assert.equal(result.annotations[0].row, 2);
    assert.equal(result.annotations[0].column, 6);
});

test("same-line annotations are not shifted by PINP tag replacement", () => {
    const result = check('<pinp> $a = ; </pinp>');
    assert.equal(result.annotations[0].row, 0);
    assert.equal(result.annotations[0].column, 12);
});

test("bad code after external compiler content is still checked", () => {
    assert.equal(check('<compile:less><pinp>ignored</compile:less><pinp> $a = ; </pinp>').event, "error");
});

test("inline PHP mode keeps working", () => {
    assert.equal(check('$a = $b ?? 1;', true).event, "ok");
    assert.equal(check('$a = ;', true).event, "error");
});

test("correction clears an earlier syntax annotation", () => {
    let source = template('$a = ;');
    const events = [];
    const worker = {
        doc: {getValue: () => source},
        sender: {emit: event => events.push(event)}
    };
    Worker.prototype.onUpdate.call(worker);
    source = template('$a = $b ?? 1;');
    Worker.prototype.onUpdate.call(worker);
    assert.deepEqual(events, ["error", "ok"]);
});

test("Ace worker constructor and inline options use the same validator", () => {
    const events = [];
    const worker = new Worker({
        on() {},
        emit: event => events.push(event)
    });
    worker.doc.setValue(template('return "abc" |> strlen(...);'));
    worker.onUpdate();
    worker.setOptions({inline: true});
    worker.doc.setValue('return $value ?? "default";');
    worker.onUpdate();
    worker.doc.setValue('$value = ;');
    worker.onUpdate();
    assert.deepEqual(events, ["ok", "ok", "error"]);
});
