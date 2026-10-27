# IlBronza Courses

Pacchetto Laravel per gestire corsi, sessioni, presenze e responsabilità formative degli operatori.

## Compatibilità Laravel

I vincoli Composer delle dipendenze Laravel e delle relative integrazioni indicano soltanto una versione minima, senza un limite sulle major successive. La scelta delle versioni concrete spetta all’applicazione ospite e al suo `composer.lock`; la compatibilità del codice va verificata con i test.

## Attestati PDF

`CourseCertificatePdfHelper` è un helper concreto: genera un attestato A4
orizzontale con header, discente, corso, dichiarazioni e firme. Non richiede
controller, route o modifiche ai modelli dell'applicazione. Il rendering usa
[Dompdf](https://github.com/dompdf/dompdf), dichiarato nelle dipendenze Composer.

### Utilizzo attuale con Ecostudio

Accetta sia un oggetto restituito dal query builder sia il pivot Eloquent
`App\Models\Clientity\CourseWorker`:

```php
use IlBronza\Courses\Helpers\Certificates\CourseCertificatePdfHelper;
use Illuminate\Support\Facades\DB;

$participation = DB::connection('old')->table('course_workers')
    ->where('id', $courseWorkerId)->firstOrFail();

$helperClass = config('courses.helpers.certificates', CourseCertificatePdfHelper::class);
$helper = new $helperClass($participation);

$path = $helper->save();        // Genera e salva il PDF, restituisce il percorso.
$path = $helper->getFilePath(); // Restituisce il percorso senza generare file.
$pdf = $helper->generate();     // Restituisce i byte PDF, senza salvarli.
$html = $helper->renderHtml();  // Anteprima HTML.
// return $helper->download();  // Salva e restituisce la risposta di download.
```

L'adapter `LegacyCourseWorkerCertificateData` usa il trait
`ExtractsLegacyCourseWorkerCertificateData`. Legge esplicitamente dalla
connessione `courses.certificates.legacy_connection`, inizialmente `old`:

- `courses`, tramite `course_workers.course_id`: alias, nome e ore;
- `workers`, tramite `worker_id`: cognome, nome, codice fiscale e ATECO;
- `companies`, esclusivamente tramite il `company_id` della partecipazione;
- `company_worker`: mansione dell'associazione attiva più recente per quella
  coppia azienda/lavoratore;
- la riga ricevuta: completamento, date teoria/pratica, sede, modalità
  e-learning e nominativi dei docenti in `teacher_string`.

Le relazioni Eloquent non vengono usate: nell'applicazione Ecostudio `course()`
punta al catalogo nuovo, mentre qui servono i dati legacy. I record correlati
sono mantenuti in memoria per la durata dell'helper. `subscription_date` non
viene interpretata come data di frequenza iniziale; l'intervallo può essere
fornito esplicitamente. L'helper non modifica il database né ricalcola la
validità della partecipazione. Completamento, nominativo, titolo e durata
devono essere presenti per generare il PDF.

La cartella predefinita è `storage/app/temp/course-certificates`, modificabile
con `courses.certificates.temp_directory` (percorso assoluto). Il nome deriva da
`hidden_notes`, conservando maiuscole, spazi e underscore e aggiungendo `.pdf`
solo quando manca. L'importatore legacy conserva le note precedenti e aggiunge
il codice su una nuova riga: viene usata l'ultima riga non vuota. Un valore
vuoto, troppo lungo o contenente percorsi produce un errore esplicito.
`save()` rigenera e sostituisce il file allo stesso percorso; non aggiorna
`hidden_notes` e non marca l'attestato come inviato.

### Dati grafici, dichiarazioni e firme

Gli enti e i firmatari non sono dedotti dall'alias del corso. Si configurano in
`courses.certificates.defaults` oppure si passano come secondo argomento:

```php
$helper = new CourseCertificatePdfHelper($participation, [
    'header' => [
        'organizations' => [
            ['name' => 'Ecostudio', 'logo' => public_path('images/ecostudio.png')],
            ['name' => 'Ente patrocinante'],
        ],
        'subtitle' => 'Riferimenti normativi del corso',
    ],
    'course' => [
        'title' => 'Titolo completo da stampare',
        'classification' => 'Aziende Gruppo B e C',
        'delivery_mode' => 'mista',
        'starts_on' => '2026-09-29',
    ],
    'disclaimer' => [
        'paragraphs' => ['Testo relativo a organizzazione e riferimenti normativi.'],
        'assessment' => 'Verifica finale superata con esito positivo.',
        'notes' => ['Parte teorica svolta in modalità videoconferenza.'],
        'place' => 'Colceresa',
        'issued_on' => '2026-10-19',
    ],
    'signatures' => [
        ['role' => 'Responsabile dell’ente', 'name' => 'Nominativo',
         'image' => storage_path('app/certificates/firma.png')],
    ],
]);
```

Le immagini possono essere file PNG/JPEG/GIF locali o data URI degli stessi
formati; vengono incorporate nel documento. Le firme sono disposte fino a
quattro per riga. Tutti i testi sono sottoposti a escaping. Le note riservate
della partecipazione non vengono stampate e un esito positivo della verifica
non viene assunto automaticamente. La data di emissione, se non fornita, è
la data corrente; per una ristampa con la data originale, fornirla esplicitamente.
Se i contenuti eccedono una pagina il salvataggio si interrompe con un errore.

Si possono inoltre impostare dati per alias in
`courses.certificates.courses['PS_A']`, con le stesse sezioni dell'esempio.
La precedenza è: default, dati estratti, dati configurati per alias, argomenti
espliciti. Gli elenchi vengono sostituiti interamente. `signatures = null` nei
default conserva i docenti estratti; `signatures = []` li rimuove.

### Contratto per il modello futuro

`IlBronza\Courses\Contracts\CourseCertificateData` è l'interfaccia da implementare
sul futuro modello della partecipazione, oppure su un suo adapter:

```php
public function getCourseAlias(): string;
public function getHeaderData(): array;
public function getParticipantData(): array;
public function getCourseData(): array;
public function getDisclaimerData(): array;
public function getSignaturesData(): array;
public function getFilePath(): string;
```

Il trait non sostituisce l'interfaccia: fornisce solo l'implementazione legacy.
Quando l'oggetto ricevuto implementa l'interfaccia, l'helper lo usa direttamente
e non accede a `old`. I metodi restituiscono le sezioni dell'esempio; le chiavi
minime sono `participant.name` e `course.title`, `course.hours`, `course.ends_on`.
Le date sono stringhe `Y-m-d`. `getFilePath()` restituisce la destinazione
assoluta comprensiva del nome file, senza effetti collaterali. Il trait
`ResolvesCertificateFilePath` è riutilizzabile anche sul modello futuro:
richiede solamente `getCertificateFilename()`.

### Helper specifici opzionali

È registrato solo il generico in `courses.helpers.certificates`. La mappa
`courses.models.course.helpers.certificates` è inizialmente vuota. Il suo
costruttore legge l'alias dalla sorgente e, quando è associata una sottoclasse
concreta, la istanzia con la stessa sorgente e le delega i metodi. Le sottoclassi
non ripetono la risoluzione: `parent::__construct()` non provoca ricorsioni.

```php
// Nell'applicazione ospitante, solo quando esiste davvero la sottoclasse:
'certificates' => [
    'PS_A' => App\Helpers\Courses\Certificates\PsCertificateHelper::class,
],
```

Le sottoclassi possono sovrascrivere i cinque getter delle sezioni,
`getViewName()` e `getFilePath()`. Una classe configurata inesistente, astratta
o incompatibile produce un errore; un alias senza associazione usa il generico.
Il template Blade predefinito è `courses::certificates.default` ed è sostituibile
tramite il normale override Laravel in
`resources/views/vendor/courses/certificates/default.blade.php`.

Questa guida è rivolta a chi deve mantenere o estendere il calcolo della **validità delle responsabilità**. È la parte che, partendo dai corsi completati di un operatore, aggiorna per ogni responsabilità:

- `valid`: se la responsabilità è valida;
- `valid_to`: fino a quando è valida;
- `parsed_at`: quando è stata ricalcolata;
- `errors`: il motivo dell’eventuale non validità.

> I nomi delle classi storiche contengono il refuso `Validith`. È intenzionale: non rinominarli senza un refactoring completo dei riferimenti.

## Cosa copre il package

Il package non è solo il motore di validità. Fornisce il dominio e le migrazioni per queste aree:

| Area | Entità principali | Scopo |
| --- | --- | --- |
| Catalogo | `Course`, `Responsibility` | Definire corsi e tipologie di responsabilità. |
| Progettazione | `Date`, `CourseTheme::program` | Definire le tipologie di data e le ore obbligatorie; suddividerle in lezioni per ciascuna tematica. |
| Erogazione | `CourseSession`, `DateSession` | Creare le edizioni dalle tematiche e calendarizzare le lezioni generate. |
| Docenti | `Teacher`, `CourseSession::teachers()` | Gestire l’anagrafica e assegnare uno o più docenti a ciascuna edizione. |
| Partecipazione | `OperatorCourseSession` | Collegare gli operatori alle edizioni e conservarne stato, completamento ed esito. |
| Storico legacy | Dati `course_workers` e `company_worker` | Leggere corsi e rapporti del lavoratore dalla connessione legacy nell’integrazione Ecostudio. |
| Responsabilità | `OperatorResponsibility`, `ClientOperatorResponsibility` | Assegnare responsabilità agli operatori e calcolarne il relativo stato di validità. |

Le migrazioni del package creano le tabelle con prefisso `courses__`. Le regole di business sulla validità, invece, sono specifiche del progetto Ecostudio e vivono nell’applicazione ospitante.

## Partecipazioni alle edizioni

`OperatorCourseSession` collega un `Operator` a una `CourseSession`. La tabella
`courses__operator_course_sessions` contiene UUID, `operator_id`,
`course_session_id`, `client_id`, `status`, `completed_at`, `result`, `note`, timestamp e
cancellazione logica. Stato, completamento, esito e note sono facoltativi;
stato ed esito sono stringhe libere, senza un flusso imposto dal package.
Il corso si ricava tramite `courseSession->course`.

`client_id` è un UUID nullable e indicizzato, predisposto per l’azienda della
partecipazione. Per ora rimane `null` quando non viene fornito e non viene
dedotto dai rapporti dell’operatore. Il riferimento non ha un vincolo esterno,
per consentire la reimportazione delle aziende anche quando cambiano gli ID.
La migrazione aggiuntiva lascia a `null` anche le partecipazioni già presenti.

```php
use IlBronza\Courses\Models\OperatorCourseSession;

$participation = OperatorCourseSession::gpc()::create([
    'operator_id' => $operator->getKey(),
    'course_session_id' => $edition->getKey(),
    'status' => 'iscritto',
]);

$participation->update([
    'status' => 'completato',
    'completed_at' => '2026-10-19 18:00:00',
    'result' => 'superato',
]);
```

Le relazioni sono `OperatorCourseSession::operator()`,
`OperatorCourseSession::courseSession()`, `OperatorCourseSession::client()` e
`CourseSession::operatorCourseSessions()`. Il trait `OperatorUsesCoursesTrait`,
da usare sul modello dell’operatore nell’applicazione ospitante, espone
`operatorCourseSessions()` per consultarne lo storico.

Nel menu **Gestione corsi → Partecipazioni alle edizioni** sono disponibili
**Recenti** (partecipazioni inserite negli ultimi 12 mesi), **Tutti** e
**Per tipo**, con una voce per ogni responsabilità ordinata per descrizione.
Il tipo è la responsabilità del corso dell’edizione (`Course::common_alias`).
Gli elenchi mostrano operatore, corso, responsabilità, edizione, stato,
completamento, esito, note e data di inserimento, dalle più recenti.

Le checkbox permettono di selezionare le partecipazioni. I due pulsanti POST
**Scarica PDF** e **Invia attestati** operano solo sulle righe selezionate.

Le richieste vengono inviate via AJAX, senza cambiare pagina. Gli errori
indicano l’operatore, l’edizione e il campo da completare; per esempio una data
di completamento mancante. Se non ci sono righe selezionate, il messaggio
invita a selezionare almeno una partecipazione. PDF e ZIP vengono scaricati
solo dopo una risposta riuscita; anche l’esito dell’invio email resta visibile
nella pagina della tabella.

- **Scarica PDF** restituisce direttamente il PDF per una partecipazione,
  oppure uno ZIP con tutti i PDF per una selezione multipla. Il file ZIP
  temporaneo viene eliminato dopo lo scaricamento.
- **Invia attestati** invia subito tramite il mailer Laravel all’indirizzo
  principale restituito da `Operator::getEmail()`. Ogni operatore riceve una
  sola email con tutti i suoi attestati selezionati; non si possono passare
  destinatari alternativi nella richiesta.

Entrambe le azioni verificano selezione e permessi. Prima di inviare email
vengono controllati tutti i destinatari e generati tutti i PDF: un indirizzo
mancante/non valido o dati incompleti impediscono l’intero invio. Se il mailer
fallisce per un operatore, gli altri invii proseguono e la pagina mostra gli
invii riusciti e quelli non riusciti.

Per `OperatorCourseSession`, l’helper PDF usa il catalogo attuale: operatore,
corso, ore copiate nelle lezioni dell’edizione, date effettivamente pianificate,
`completed_at` e docenti dell’edizione. La data di completamento è obbligatoria
per l’attestato; il periodo desiderato dell’edizione e le note private non
vengono usati come dati di frequenza. Il nome del PDF include l’UUID della
partecipazione. Restano disponibili template, configurazioni e helper per
alias descritti nella sezione attestati. Lo ZIP richiede l’estensione PHP
`ext-zip`; l’invio usa la configurazione `mail` dell’applicazione ospitante.

La coppia operatore/edizione è univoca anche dopo una cancellazione logica:
per riattivare una partecipazione eliminata si usa `restore()`. Le nuove
partecipazioni richiedono un operatore e un’edizione attivi. Quelle esistenti
rimangono consultabili e aggiornabili dopo la cancellazione logica dei genitori.
La migrazione viene caricata dal provider con `php artisan migrate` e richiede
che la tabella degli operatori sia già presente.

## Tematiche dei corsi

`CourseTheme` rappresenta una tematica specifica di un corso, per esempio
Farmacia o Parafarmacia. Ogni tematica appartiene a un `Course` tramite
`course_id`; responsabilità e validità rimangono definite sul corso.
Le relazioni sono `Course::themes()` e `CourseTheme::course()`.

La tabella `courses__course_themes` contiene UUID, corso, nome, descrizione
facoltativa, JSON `ateco`, timestamp e cancellazione logica. Il JSON usa il
formato dell'editor a righe del package Form:

```json
[
  {"code": "01.11.00"},
  {"code": "02.10.00"}
]
```

I codici sono stringhe: punti e zeri iniziali vengono conservati. L'elenco è
facoltativo; le righe vuote vengono scartate e i duplicati rifiutati dal form.
Il campo è predisposto per la futura selezione delle aziende destinatarie;
non esegue ancora abbinamenti o verifiche sul catalogo ATECO.

La voce **Tematiche dei corsi** nel menu espone elenco, creazione, dettaglio,
modifica ed eliminazione. Il dettaglio di ogni corso mostra le sue tematiche
e il pulsante **Nuova tematica**, che preseleziona il corso. La cancellazione
del corso tramite il CRUD cancella logicamente anche le sue tematiche.

Per attivare la tabella nell'applicazione ospitante, eseguire `php artisan migrate`.
Le edizioni si riferiscono alla tematica tramite `course_theme_id`.

## Tipologie di data, programmi ed edizioni

Il percorso di progettazione è:

```text
Corso FL_G
  ├─ Tipologia Teoria: 8 ore obbligatorie
  ├─ Tipologia Pratica: 4 ore obbligatorie
  └─ Tematica Farmacia
       ├─ Programma: Teoria 1 (4h), Teoria 2 (4h), Esercitazione (4h)
       └─ Edizione di ottobre
            └─ Tre lezioni indipendenti, ciascuna con giorno e ora da assegnare
```

### Tipologie del corso

`Date` è il modello delle **tipologie di data**, per esempio Teoria e Pratica.
Appartiene a un corso (`Course::dates()`) e definisce nome, descrizione,
ore obbligatorie e ordine. Il menu **Tipologie di data** espone il CRUD;
dal dettaglio del corso, **Nuova tipologia di data** preseleziona il corso.

Le ore ammettono due decimali e devono essere maggiori di zero. I vincoli di
progettazione usano le ore di ciascuna tipologia; il campo storico `Course::hours`
non viene usato per sostituire questi vincoli né aggiornato automaticamente.

### Programma della tematica

Il campo JSON `CourseTheme::program` contiene le lezioni in ordine:

```json
[
  {"date_id": "uuid-tipologia-teoria", "name": "Teoria 1", "hours": "4.00", "description": "Introduzione"},
  {"date_id": "uuid-tipologia-teoria", "name": "Teoria 2", "hours": "4.00"},
  {"date_id": "uuid-tipologia-pratica", "name": "Esercitazione", "hours": "4.00"}
]
```

Il form di creazione da un corso e quello di modifica di una tematica senza
programma propongono una lezione per tipologia, con le ore previste dal corso.
La traccia è modificabile prima del salvataggio: si possono suddividere le ore
in più lezioni e specificarne i contenuti. È possibile lasciare il programma
vuoto, conservando la tematica nello stato **Da progettare**.

Un programma non vuoto deve rispettare **esattamente le ore di ogni tipologia**.
Con 8 ore di teoria e 4 di pratica, 9+3 non è valido. Non sono accettate tipologie
di altri corsi, eliminate, ore negative o più di due decimali. Il confronto
avviene sommando centesimi di ora interi. I controlli sono applicati anche dal
modello, quindi valgono fuori dai form HTTP.

Una tipologia già usata in un programma non può essere eliminata; aggiungere
tipologie o modificarne le ore non può invalidare i programmi salvati. Per
riprogettare le regole del corso occorre prima svuotare i programmi interessati.
Una tematica che ha già edizioni non può essere spostata su un altro corso.

### Edizioni e date reali

`CourseSession` rappresenta un'edizione: tematica, corso derivato dalla tematica,
nome e periodo desiderato facoltativo. Si crea dal menu **Edizioni**, dal corso
o dalla tematica. La tematica deve avere un programma completo.

Alla creazione, in un'unica transazione, il programma viene copiato in record
`DateSession` (`CourseSession::dateSessions()`). Ogni lezione conserva tipologia,
nome della tipologia, titolo, descrizione, ore e posizione. `starts_at` è inizialmente
nullo; il dettaglio dell'edizione permette di aprire e calendarizzare ogni lezione.
Il form della lezione modifica titolo, descrizione e giorno/ora; ore e tipologia
restano quelle copiate. Le lezioni vengono generate una sola volta e non hanno
creazione o eliminazione autonoma tramite il CRUD.

Le modifiche al programma valgono per le edizioni successive. Le edizioni già
create conservano le proprie lezioni, anche se il programma viene svuotato o la
tematica viene eliminata. Corso e tematica di un'edizione non sono riassegnabili.
Eliminare un'edizione tramite il CRUD cancella logicamente le sue lezioni;
eliminare il corso tramite il CRUD cancella anche tematiche, tipologie ed edizioni.

La migrazione `2026_10_03_000022_create_course_planning_tables.php` attiva le tabelle
`courses__dates`, `courses__course_sessions`, `courses__date_sessions` e il programma
della tematica. Le precedenti bozze in `database/` (fuori da `database/migrations/`)
non sono caricate dal service provider e non vanno eseguite insieme a questa
migrazione. Ogni lezione reale appartiene a una sola edizione; il vecchio abbozzo
del collegamento molti-a-molti non viene utilizzato.

La suite `CoursePlanningTest` verifica i vincoli per tipologia, le ore frazionarie,
la generazione atomica, l'indipendenza delle edizioni, la calendarizzazione e le
schermate CRUD. La suite `CourseThemeTest` copre anche ATECO e relazioni delle tematiche.

## Docenti delle edizioni

La voce **Docenti** in Gestione corsi espone elenco, creazione, dettaglio,
modifica ed eliminazione dell’anagrafica. Ogni docente ha un nome obbligatorio
(nome e cognome), email, telefono e note facoltativi. Il dettaglio mostra le
edizioni associate.

Nei form di creazione e modifica di un’edizione, **Docenti** permette di
selezionare più persone dall’archivio. L’assegnazione è facoltativa e specifica
dell’edizione: lo stesso docente può tenere più edizioni e due edizioni dello
stesso corso possono avere docenti diversi. Deselezionando tutti i docenti si
svuota l’assegnazione. L’elenco delle edizioni mostra i nomi dei docenti;
il dettaglio permette di aprire le rispettive schede.

Le relazioni `CourseSession::teachers()` e `Teacher::courseSessions()` usano
la tabella ponte `courses__course_session_teacher`, con coppie univoche.
L’eliminazione logica di un docente lo rimuove dall’archivio selezionabile per
nuove assegnazioni e conserva quelle storiche. Un docente eliminato già
associato può essere mantenuto o rimosso dal form dell’edizione.
Eliminare un’edizione non elimina i docenti.

Eseguire `php artisan migrate` nell’applicazione ospitante per applicare
`2026_10_15_000023_create_teachers_and_course_session_teacher_tables.php`.

## Dove vive il codice

Il package contiene l’infrastruttura generica; l’applicazione ospitante (`Ecostudio`) contiene le regole aziendali e la mappa delle responsabilità.

| Cosa | File |
| --- | --- |
| Modello di una responsabilità assegnata a un operatore | `src/Models/OperatorResponsibility.php` |
| Scope Eloquent della responsabilità | `src/Traits/Models/OperatorResponsibilityScopesTrait.php` |
| Motore comune di un helper | `src/Helpers/OperatorCourses/OperatorResponsibilityValidityHelperGeneral.php` |
| Elaborazione batch delle responsabilità non ancora calcolate | `src/Helpers/OperatorResponsibilities/OperatorResponsibilityValidityHelper.php` |
| Helper base dell’app, che legge i corsi legacy | Applicazione ospitante: `app/Helpers/Courses/Validity/OperatorResponsibilityValidityHelperBase.php` |
| Mappa `responsabilità → helper` dell’app | Applicazione ospitante: `config/courses.php` |
| Elaborazione delle responsabilità di un solo operatore | Applicazione ospitante: `app/Helpers/Courses/Validity/OperatorGeneralResponsibilityValidityHelper.php` |

## Modello mentale

```text
Operator
  └─ operatorResponsibilities
       ├─ responsibility_id = "FL"  ──► helper FL  ──► valid / valid_to / errors
       ├─ responsibility_id = "PS"  ──► helper PS  ──► valid / valid_to / errors
       └─ responsibility_id = "ANT" ──► helper ANT ──► valid / valid_to / errors

Ogni helper legge i CourseWorker legacy del worker associato all’operator.
```

Una `OperatorResponsibility` non è il completamento di un singolo corso: è lo stato aggregato di una tipologia, per esempio `FL`, `PS` o `ANT`.

## Dati usati dagli helper

Nell’integrazione Ecostudio, `OperatorResponsibilityValidityHelperBase` recupera i record `CourseWorker` tramite l’ID del worker legacy dell’operatore:

```php
CourseWorker::byWorker($operator->getWorkerId())->get();
```

Ogni helper dichiara gli alias dei corsi che gli interessano in `static::$includedResponsibilities`; il base helper filtra i `CourseWorker` su quell’elenco. I campi importanti sono:

| Campo | Significato nel calcolo |
| --- | --- |
| `responsibility` | Alias del corso, ad esempio `FL_G`, `FL_S`, `PS_B`. |
| `completion` | Data di completamento principale. |
| `completion_ps_t1`, `completion_ps_t2` | Date delle parti aggiuntive, usate dal base/agg corrente. |
| `expiration_date` | Scadenza già calcolata nel sistema legacy; alcuni helper la usano direttamente. |
| `flag_boh` | Eccezione legacy: consente al base/agg di accettare un corso base anche quando mancano le date delle parti PS. |

## Risultato di una validazione

Tutti gli helper concreti terminano in uno dei due metodi del motore generico:

```php
$this->setValidWithDate($date);
$this->setNotValid();
```

`setValidWithDate()` salva `valid = true`, `valid_to = $date`, `parsed_at = now()` e pulisce gli errori.

`setNotValid()` salva `valid = false`, azzera `valid_to`, imposta `parsed_at = now()` e concatena l’array `$problems` nel campo `errors` usando ` | ` come separatore.

## Come eseguire i calcoli

### Una responsabilità singola

Ogni helper estende `OperatorResponsibilityValidityHelperGeneral`, quindi può essere invocato direttamente con il record da calcolare:

```php
OperatorResponsibilityValidityHelperFL::parse($operatorResponsibility);
```

### Tutte le responsabilità di un operatore

Questo è il punto di ingresso da usare quando cambiano i corsi di un lavoratore e si vuole ricalcolare il suo solo operatore:

```php
use App\Helpers\Courses\Validity\OperatorGeneralResponsibilityValidityHelper;

OperatorGeneralResponsibilityValidityHelper::parse($operator);
```

L’helper percorre `$operator->operatorResponsibilities`, trova la classe configurata per ogni `responsibility_id` e chiama `::parse()` su quella classe. Una responsabilità senza helper configurato viene ignorata: non viene modificata né genera un errore.

### Tutte le responsabilità di una tipologia

Ogni helper concreto dichiara `static string $responsibility`, per esempio `FL`. Il metodo ereditato può ricalcolare tutti gli operatori che hanno quella tipologia:

```php
OperatorResponsibilityValidityHelperFL::parseByResponsibility();
```

Il flusso è:

1. prende `static::$responsibility` dalla classe chiamante;
2. recupera la classe configurata in `courses.models.responsibility.helpers.validity`;
3. cerca `OperatorResponsibility::byResponsibility('FL')`;
4. richiama `::parse()` per ogni record trovato.

Lo scope usato è:

```php
OperatorResponsibility::gpc()::byResponsibility('FL')
```

### Elaborazione batch delle sole responsabilità da calcolare

Il package espone anche:

```php
OperatorResponsibilityValidityHelper::parse();
```

Questo metodo elabora solo le responsabilità con `parsed_at` nullo (`toParse()`), ordinandole prima per quelle mai elaborate e poi per data di parsing. A differenza dell’orchestratore per operatore, si aspetta che ogni responsabilità trovata abbia un helper configurato: una chiave mancante porta a un errore PHP al momento della chiamata statica.

## Regole attualmente implementate

Le classi effettivamente mappate sono definite nel `config/courses.php` dell’applicazione ospitante. Lo stato sotto descrive il comportamento del codice attuale, non una prescrizione normativa.

| Responsabilità | Helper | Stato / regola |
| --- | --- | --- |
| `FL` | `OperatorResponsibilityValidityHelperFL` | Implementato. Vedi la sezione dedicata. |
| `PRI` | `OperatorResponsibilityValidityHelperPRI` | Implementato con una regola transitoria specifica. |
| `ANT` | `OperatorResponsibilityValidityHelperANT` | Implementato con base + aggiornamento. |
| `ANT2` | `OperatorResponsibilityValidityHelperANT2` | Implementato con base + aggiornamento. |
| `ANT3` | `OperatorResponsibilityValidityHelperANT3` | Implementato con base + aggiornamento. |
| `PREP` | `OperatorResponsibilityValidityHelperPREP` | Implementato con base + aggiornamento. |
| `PS` | `OperatorResponsibilityValidityHelperPS` | Implementato con base + aggiornamento. |
| `DL` | `OperatorResponsibilityValidityHelperDL` | Implementato con base + aggiornamento. |
| `DIR`, `FL_S_EL`, `RSPP`, `RLS`, `COVID-AMBULATORI`, `HACCP_*` | rispettivi helper | Placeholder: l’`handle()` ritorna subito e non salva alcun esito. Non sono validazioni operative. |

### FL — formazione lavoratori

La regola FL è volutamente esplicita e usa solo le date di `completion`, mai `expiration_date`.

#### Prerequisiti indispensabili

Per poter essere valida, la responsabilità `FL` richiede entrambi:

1. almeno un `FL_G` completato;
2. almeno un `FL_S` **oppure** `FL_S_EL` completato.

Se anche uno solo dei prerequisiti manca, viene salvato `valid = false` con uno di questi errori:

- `FL_G mancante o non completato`;
- `FL_S o FL_S_EL mancante o non completato`.

#### Calcolo della data

Quando i prerequisiti ci sono, l’helper trova la data di completamento più recente tra:

- il più recente fra `FL_S` e `FL_S_EL`;
- `PREP_A`;
- `FL_A`.

Poi calcola:

```text
valid_to = data_di_completamento_più_recente + 60 mesi
```

Se `valid_to` è nel futuro, `FL` è valida; altrimenti viene salvata come non valida con errore `FL scaduto`.

Esempio:

| Corso | Completamento |
| --- | --- |
| `FL_G` | 2021-02-10 |
| `FL_S_EL` | 2022-05-01 |
| `PREP_A` | 2024-01-20 |
| `FL_A` | 2023-06-15 |

La data più recente è `PREP_A` (`2024-01-20`), quindi la validità FL termina il `2029-01-20`.

### PRI

L’helper considera `PRI` e `PRIECO`. Se trova almeno un corso con `completion`, imposta una data fissa di validità al `2040-03-22`; non calcola una durata a partire dal completamento. Se non trova completamenti, salva la responsabilità come non valida.

Questa è una regola transitoria codificata nell’helper, da rivalutare prima di renderla una regola definitiva.

### Famiglia base + aggiornamento

`ANT`, `ANT2`, `ANT3`, `PREP`, `PS` e `DL` estendono `OperatorResponsibilityValidityHelperBasePlusAgg` e usano la stessa struttura.

Ogni classe dichiara:

```php
static array $includedResponsibilities;
static string $baseString;
static string $aggString;
```

La configurazione corrente è:

| Responsabilità | Base | Aggiornamento |
| --- | --- | --- |
| `ANT` | `ANT_B` | `ANT_A` |
| `ANT2` | `ANT2` | `ANT_2_A` |
| `ANT3` | `ANT3_B` | `ANT3_A` |
| `PREP` | `PREP_B` | `PREP_A` |
| `PS` | `PS_B` | `PS_A` |
| `DL` | `DL_B` | `DL_A` |

Il comportamento del base/agg attuale è:

1. per il corso **base**, cerca il record con la maggiore `expiration_date` che abbia `completion`, `completion_ps_t1` e `completion_ps_t2`;
2. se le tre date non ci sono ma `flag_boh` è attivo, usa `expiration_date`, oppure calcola la scadenza dal completamento tramite `addValidityToDate()`;
3. per l’**aggiornamento**, usa la maggiore `expiration_date` disponibile;
4. con base e aggiornamento presenti, la data finale è la maggiore tra le due;
5. con il solo base, la responsabilità è valida solo se la sua data è futura; il codice registra inoltre l’aggiornamento come mancante;
6. senza una data base valida, la responsabilità non è valida.

### Avvertenza sul base/agg

Il controllo delle colonne `completion_ps_t1` e `completion_ps_t2` è nel base helper comune, quindi oggi viene applicato anche a `ANT`, `ANT2`, `ANT3`, `PREP` e `DL`, non soltanto a `PS`. Questa è l’implementazione presente: prima di modificarla, decidere esplicitamente se è una regola voluta o un residuo della logica PS.

## Aggiungere una nuova responsabilità

1. Creare un helper in `app/Helpers/Courses/Validity/` dell’applicazione ospitante.
2. Estendere `OperatorResponsibilityValidityHelperBase` oppure `OperatorResponsibilityValidityHelperBasePlusAgg` se la regola è realmente quella base + aggiornamento.
3. Impostare la chiave, gli alias dei corsi letti e la logica di `handle()`.
4. Registrare la chiave in `config/courses.php` dell’applicazione ospitante sotto `models.responsibility.helpers.validity`.
5. Preparare casi con corso assente, corso incompleto, corso scaduto e corso valido.

Esempio minimale:

```php
class OperatorResponsibilityValidityHelperXYZ extends OperatorResponsibilityValidityHelperBase
{
    static string $responsibility = 'XYZ';

    static array $includedResponsibilities = [
        'XYZ_B',
        'XYZ_A',
    ];

    protected function handle() : void
    {
        // Leggere $this->getCourseSessions().
        // Aggiungere messaggi in $this->problems se necessario.
        // Concludere sempre con setValidWithDate() oppure setNotValid().
    }
}
```

## Checklist di manutenzione

Prima di considerare completata una modifica alle validità:

- verificare che `responsibility_id` coincida con la chiave in `config/courses.php`;
- verificare che gli alias in `$includedResponsibilities` coincidano con quelli realmente salvati nei `CourseWorker`;
- definire se la data di partenza è `completion` oppure `expiration_date`;
- verificare i prerequisiti obbligatori separatamente dalle date che prolungano la validità;
- verificare sia una scadenza futura sia una scaduta;
- verificare il contenuto di `errors` per un caso non valido;
- eseguire almeno il controllo sintattico:

```bash
php -l percorso/del/file.php
```

## Limiti noti

- `OperatorResponsibilityValidityHelperGeneral::getCourseSessions()` nel package contiene un `dd()` intenzionale: gli helper dell’app devono passare da `OperatorResponsibilityValidityHelperBase`, che lo sostituisce con il recupero corretto dei `CourseWorker` legacy.
- Gli helper placeholder elencati nella tabella non producono alcun esito; lasciarli mappati può far sembrare che una responsabilità sia stata elaborata quando non è successo nulla.
- Il package non contiene ancora una suite automatizzata di test per queste regole. Ogni nuova logica va prima verificata con una matrice di casi reali o fixture dedicate.
