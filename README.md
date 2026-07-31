# IlBronza Courses

Pacchetto Laravel per gestire corsi, sessioni, presenze e responsabilità formative degli operatori.

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
| Erogazione | `CourseSession`, `Date`, `DateSession` | Pianificare un corso, le sue date e le sessioni effettive. |
| Partecipazione | `WorkerCourseSession`, `Attendance` | Collegare lavoratori alle sessioni e registrarne la presenza. |
| Storico lavoratore | `CourseWorker`, `CompanyWorker` | Rappresentare corsi e rapporti del lavoratore, compreso l’accesso ai dati legacy nell’integrazione Ecostudio. |
| Responsabilità | `OperatorResponsibility`, `ClientOperatorResponsibility` | Assegnare responsabilità agli operatori e calcolarne il relativo stato di validità. |

Le migrazioni del package creano le tabelle con prefisso `courses__`. Le regole di business sulla validità, invece, sono specifiche del progetto Ecostudio e vivono nell’applicazione ospitante.

## Dove vive il codice

Il package contiene l’infrastruttura generica; l’applicazione ospitante (`Ecostudio`) contiene le regole aziendali e la mappa delle responsabilità.

| Cosa | File |
| --- | --- |
| Modello di una responsabilità assegnata a un operatore | `src/Models/OperatorResponsibility.php` |
| Scope Eloquent della responsabilità | `src/Traits/Models/OperatorResponsibilityScopesTrait.php` |
| Motore comune di un helper | `src/Helpers/OperatorCourses/OperatorCourseValidithHelperGeneral.php` |
| Elaborazione batch delle responsabilità non ancora calcolate | `src/Helpers/OperatorResponsibilities/OperatorResponsibilityValidityHelper.php` |
| Helper base dell’app, che legge i corsi legacy | Applicazione ospitante: `app/Helpers/Courses/Validity/OperatorCourseValidithHelperBase.php` |
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

Nell’integrazione Ecostudio, `OperatorCourseValidithHelperBase` recupera i record `CourseWorker` tramite l’ID del worker legacy dell’operatore:

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

Ogni helper estende `OperatorCourseValidithHelperGeneral`, quindi può essere invocato direttamente con il record da calcolare:

```php
OperatorCourseValidithHelperFL::parse($operatorResponsibility);
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
OperatorCourseValidithHelperFL::parseByResponsibility();
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
| `FL` | `OperatorCourseValidithHelperFL` | Implementato. Vedi la sezione dedicata. |
| `PRI` | `OperatorCourseValidithHelperPRI` | Implementato con una regola transitoria specifica. |
| `ANT` | `OperatorCourseValidithHelperANT` | Implementato con base + aggiornamento. |
| `ANT2` | `OperatorCourseValidithHelperANT2` | Implementato con base + aggiornamento. |
| `ANT3` | `OperatorCourseValidithHelperANT3` | Implementato con base + aggiornamento. |
| `PREP` | `OperatorCourseValidithHelperPREP` | Implementato con base + aggiornamento. |
| `PS` | `OperatorCourseValidithHelperPS` | Implementato con base + aggiornamento. |
| `DL` | `OperatorCourseValidithHelperDL` | Implementato con base + aggiornamento. |
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

`ANT`, `ANT2`, `ANT3`, `PREP`, `PS` e `DL` estendono `OperatorCourseValidithHelperBasePlusAgg` e usano la stessa struttura.

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
2. Estendere `OperatorCourseValidithHelperBase` oppure `OperatorCourseValidithHelperBasePlusAgg` se la regola è realmente quella base + aggiornamento.
3. Impostare la chiave, gli alias dei corsi letti e la logica di `handle()`.
4. Registrare la chiave in `config/courses.php` dell’applicazione ospitante sotto `models.responsibility.helpers.validity`.
5. Preparare casi con corso assente, corso incompleto, corso scaduto e corso valido.

Esempio minimale:

```php
class OperatorCourseValidithHelperXYZ extends OperatorCourseValidithHelperBase
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

- `OperatorCourseValidithHelperGeneral::getCourseSessions()` nel package contiene un `dd()` intenzionale: gli helper dell’app devono passare da `OperatorCourseValidithHelperBase`, che lo sostituisce con il recupero corretto dei `CourseWorker` legacy.
- Gli helper placeholder elencati nella tabella non producono alcun esito; lasciarli mappati può far sembrare che una responsabilità sia stata elaborata quando non è successo nulla.
- Il package non contiene ancora una suite automatizzata di test per queste regole. Ogni nuova logica va prima verificata con una matrice di casi reali o fixture dedicate.
