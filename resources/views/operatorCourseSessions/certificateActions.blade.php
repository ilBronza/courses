<script>
(function () {
    if (window.courseCertificateActionsRegistered) return;
    window.courseCertificateActionsRegistered = true;

    function showMessages(feedback, messages, status) {
        feedback.replaceChildren();
        const alert = document.createElement('div');
        alert.className = 'uk-alert-' + status;
        const list = document.createElement('ul');
        messages.forEach(function (message) {
            const item = document.createElement('li');
            item.textContent = message;
            list.appendChild(item);
        });
        alert.appendChild(list);
        feedback.appendChild(alert);
    }

    function errorMessages(payload, fallback) {
        const errors = Object.values(payload.errors || {}).flat().filter(function (message) {
            return typeof message === 'string' && message.length > 0;
        });
        if (errors.length) return errors;
        return [payload.message || fallback];
    }

    function downloadName(disposition, type) {
        const encoded = disposition.match(/filename\*=UTF-8''([^;]+)/i);
        if (encoded) {
            try { return decodeURIComponent(encoded[1]); } catch (error) { /* Use the plain filename. */ }
        }
        const plain = disposition.match(/filename="([^"]+)"|filename=([^;]+)/i);
        return plain ? (plain[1] || plain[2]).trim() : (type === 'application/pdf' ? 'attestato.pdf' : 'attestati.zip');
    }

    document.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-course-certificate-action]');
        if (!button) return;
        event.preventDefault();

        const tableId = button.dataset.tableid;
        const feedback = document.getElementById('course-certificate-feedback-' + tableId);
        if (!feedback || feedback.dataset.busy === 'true') return;

        let table;
        let ids;
        try {
            table = window.jQuery(document.getElementById(tableId)).DataTable();
            ids = table.rows({selected: true}).data().pluck(table.init().rowId).toArray();
        } catch (error) {
            showMessages(feedback, ['La tabella non è pronta. Attendi il caricamento e riprova.'], 'danger');
            return;
        }
        if (!ids.length) {
            showMessages(feedback, ['Seleziona almeno una partecipazione nella tabella.'], 'danger');
            return;
        }

        const buttons = Array.from(document.querySelectorAll('[data-course-certificate-action]')).filter(function (item) {
            return item.dataset.tableid === tableId;
        });
        feedback.dataset.busy = 'true';
        buttons.forEach(function (item) { item.setAttribute('aria-disabled', 'true'); });
        showMessages(feedback, ['Elaborazione degli attestati in corso…'], 'primary');

        try {
            const response = await window.fetch(button.href, {
                method: 'POST',
                credentials: 'same-origin',
                redirect: 'error',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': feedback.dataset.csrfToken
                },
                body: JSON.stringify({ids: ids})
            });
            const type = (response.headers.get('Content-Type') || '').split(';')[0].trim().toLowerCase();
            const fallback = response.status === 419 || response.status === 401
                ? 'La sessione è scaduta. Accedi di nuovo prima di riprovare.'
                : response.status === 403
                    ? 'Non hai i permessi per eseguire questa operazione.'
                    : 'Impossibile completare l’operazione sugli attestati. Riprova; se il problema persiste, contatta l’assistenza.';

            if (!response.ok) {
                const payload = type === 'application/json' ? await response.json() : {};
                showMessages(feedback, errorMessages(payload, fallback), 'danger');
                return;
            }

            if (button.dataset.courseCertificateAction === 'send' && type === 'application/json') {
                const payload = await response.json();
                const errors = Object.values(payload.errors || {}).flat();
                const messages = payload.message ? [payload.message] : [];
                showMessages(feedback, messages.concat(errors), errors.length ? 'danger' : 'success');
                return;
            }

            if (button.dataset.courseCertificateAction !== 'download' || !['application/pdf', 'application/zip'].includes(type)) {
                showMessages(feedback, ['Il server non ha restituito un PDF o uno ZIP valido. Nessun file è stato scaricato.'], 'danger');
                return;
            }

            const blob = await response.blob();
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = downloadName(response.headers.get('Content-Disposition') || '', type);
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.setTimeout(function () { URL.revokeObjectURL(url); }, 60000);
            showMessages(feedback, ['Scaricamento degli attestati avviato.'], 'success');
        } catch (error) {
            showMessages(feedback, ['La richiesta non è stata completata. Controlla la connessione e riprova.'], 'danger');
        } finally {
            delete feedback.dataset.busy;
            buttons.forEach(function (item) { item.removeAttribute('aria-disabled'); });
        }
    });
})();
</script>
