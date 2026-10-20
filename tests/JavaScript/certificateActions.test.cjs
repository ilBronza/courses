const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const script = fs.readFileSync(path.join(__dirname, '../../resources/views/operatorCourseSessions/certificateActions.blade.php'), 'utf8')
    .replace(/^<script>\s*/, '').replace(/\s*<\/script>\s*$/, '');

function fixture({ids = ['participation-1'], action = 'download', response, fetchError} = {}) {
    class Element {
        constructor() { this.children = []; this.dataset = {}; this.attributes = {}; this.value = ''; }
        appendChild(child) { this.children.push(child); }
        replaceChildren() { this.children = []; }
        set textContent(text) { this.value = text; }
        get textContent() { return this.value + this.children.map(child => child.textContent).join(' '); }
        setAttribute(key, value) { this.attributes[key] = value; }
        removeAttribute(key) { delete this.attributes[key]; }
        remove() {}
        click() { state.downloads.push({href: this.href, filename: this.download}); }
    }
    const feedback = new Element();
    feedback.dataset.csrfToken = 'test-csrf-token';
    const button = new Element();
    button.dataset = {tableid: 'participations', courseCertificateAction: action};
    button.href = '/certificates/' + action;
    const table = new Element();
    const sibling = new Element();
    sibling.dataset = {...button.dataset};
    const state = {feedback, button, sibling, fetchCalls: [], downloads: [], blobs: [], timers: [], prevented: 0};
    const document = {
        body: new Element(),
        createElement: () => new Element(),
        getElementById: id => id === 'participations' ? table : feedback,
        querySelectorAll: () => [button, sibling],
        addEventListener: (type, listener) => { state.listener = listener; }
    };
    const window = {
        jQuery: node => {
            assert.equal(node, table);
            return {DataTable: () => ({
                init: () => ({rowId: 2}),
                rows: options => {
                    assert.equal(options.selected, true);
                    return {data: () => ({pluck: column => {
                        assert.equal(column, 2);
                        return {toArray: () => ids};
                    }})};
                }
            })};
        },
        fetch: async (url, options) => {
            state.fetchCalls.push({url, options});
            if (fetchError) throw fetchError;
            return typeof response === 'function' ? await response() : response;
        },
        setTimeout: callback => state.timers.push(callback)
    };
    const urls = {
        createObjectURL: blob => { state.blobs.push(blob); return 'blob:certificate'; },
        revokeObjectURL: url => { state.revoked = url; }
    };
    vm.runInNewContext(script, {document, window, URL: urls});
    state.click = () => state.listener({
        target: {closest: () => button},
        preventDefault: () => { state.prevented++; }
    });
    return state;
}

test('empty selection explains what to select and never submits or downloads', async () => {
    const state = fixture({ids: []});
    await state.click();
    assert.equal(state.prevented, 1);
    assert.equal(state.fetchCalls.length, 0);
    assert.equal(state.downloads.length, 0);
    assert.match(state.feedback.textContent, /Seleziona almeno una partecipazione/);
});

test('422 JSON displays the specific row and field error without downloading or navigating', async () => {
    const message = 'Mario Rossi — Ottobre: Manca la data di completamento <esempio>.';
    const state = fixture({response: Response.json({message: 'validation.required', errors: {'course.ends_on': [message]}}, {status: 422})});
    await state.click();
    assert.equal(state.prevented, 1);
    assert.equal(state.feedback.textContent, message);
    assert.equal(state.downloads.length, 0);
    assert.equal(state.button.attributes['aria-disabled'], undefined);
    const options = state.fetchCalls[0].options;
    assert.equal(options.method, 'POST');
    assert.equal(options.redirect, 'error');
    assert.equal(options.headers.Accept, 'application/json');
    assert.equal(options.headers['X-CSRF-TOKEN'], 'test-csrf-token');
    assert.deepEqual(JSON.parse(options.body), {ids: ['participation-1']});
});

test('successful PDF request downloads the binary response with its filename', async () => {
    const state = fixture({response: new Response('%PDF-test', {headers: {
        'Content-Type': 'application/pdf', 'Content-Disposition': 'attachment; filename="attestato.pdf"'
    }})});
    await state.click();
    assert.deepEqual(state.downloads, [{href: 'blob:certificate', filename: 'attestato.pdf'}]);
    assert.equal(await state.blobs[0].text(), '%PDF-test');
    assert.match(state.feedback.textContent, /Scaricamento.*avviato/);
    state.timers[0]();
    assert.equal(state.revoked, 'blob:certificate');
});

test('multiple selected rows download a ZIP and support UTF-8 filenames', async () => {
    const state = fixture({ids: ['first', 'second'], response: new Response('ZIP-test', {headers: {
        'Content-Type': 'application/zip', 'Content-Disposition': "attachment; filename*=UTF-8''attestati%20societ%C3%A0.zip"
    }})});
    await state.click();
    assert.deepEqual(JSON.parse(state.fetchCalls[0].options.body).ids, ['first', 'second']);
    assert.equal(state.downloads[0].filename, 'attestati società.zip');
});

test('HTML responses and expired sessions are never downloaded as PDF files', async () => {
    for (const status of [200, 419, 500]) {
        const state = fixture({response: new Response('<html>error</html>', {status, headers: {'Content-Type': 'text/html'}})});
        await state.click();
        assert.equal(state.prevented, 1);
        assert.equal(state.downloads.length, 0);
        assert.ok(state.feedback.textContent.length > 0);
        assert.equal(state.feedback.children[0].className, 'uk-alert-danger');
        if (status === 419) assert.match(state.feedback.textContent, /sessione è scaduta/);
    }
});

test('network errors keep the page and release both action buttons', async () => {
    const state = fixture({fetchError: new Error('offline')});
    await state.click();
    assert.match(state.feedback.textContent, /connessione/);
    assert.equal(state.prevented, 1);
    assert.equal(state.downloads.length, 0);
    assert.equal(state.button.attributes['aria-disabled'], undefined);
    assert.equal(state.sibling.attributes['aria-disabled'], undefined);
});

test('email response displays successful sends and recipient failures without navigation', async () => {
    const state = fixture({action: 'send', response: Response.json({
        message: 'Inviato 1 attestato a 1 operatore.', errors: {certificates: ['Invio non riuscito a anna@example.test.']}
    })});
    await state.click();
    assert.match(state.feedback.textContent, /Inviato 1 attestato/);
    assert.match(state.feedback.textContent, /Invio non riuscito a anna@example.test/);
    assert.equal(state.downloads.length, 0);
    assert.equal(state.prevented, 1);
});

test('a second click while a request is pending never sends a duplicate request', async () => {
    let resolve;
    const pending = new Promise(done => { resolve = done; });
    const state = fixture({action: 'send', response: () => pending});
    const first = state.click();
    assert.equal(state.button.attributes['aria-disabled'], 'true');
    assert.equal(state.sibling.attributes['aria-disabled'], 'true');
    await state.click();
    assert.equal(state.fetchCalls.length, 1);
    resolve(Response.json({message: 'Inviato.', errors: {}}));
    await first;
    assert.equal(state.button.attributes['aria-disabled'], undefined);
});
