# MP Order Return

## Descrizione
Questo modulo per PrestaShop sostituisce la funzionalità standard di stampa dei moduli di reso con un sistema personalizzato che genera un PDF professionale e ben strutturato. Il modulo è progettato per migliorare l'esperienza utente e fornire un documento di reso più completo e professionale sia per i clienti che per gli amministratori del negozio.

## Funzionalità Principali

### 1. Generazione PDF Avanzata
- Utilizza TCPDF per creare documenti PDF di alta qualità
- Layout professionale con intestazione, piè di pagina e sezioni ben definite
- Supporto per più pagine con gestione automatica del contenuto

### 2. Informazioni Complete
- Dati del cliente e dell'ordine
- Dettagli completi sui prodotti da restituire
- Sezione per le note e i motivi del reso
- Opzioni di restituzione (rimborso, sostituzione, buono)

### 3. Etichetta di Spedizione
- Include un'etichetta di spedizione pronta da ritagliare
- Contiene informazioni di mittente e destinatario
- Bordo tratteggiato per facilitare il ritaglio

### 4. Integrazione Completa
- Funziona sia nel back-office che nel front-office
- Sostituisce i pulsanti di stampa standard di PrestaShop
- Supporto per apertura diretta del PDF o download

## Installazione

1. Carica la cartella del modulo nella directory `/modules/` del tuo PrestaShop
2. Installa il modulo dal pannello di amministrazione
3. Il modulo si integrerà automaticamente con la pagina dei resi

## Requisiti

- PrestaShop 1.6 o superiore
- PHP 7.1 o superiore
- Libreria TCPDF (inclusa come dipendenza Composer)

## Configurazione

Il modulo non richiede configurazioni particolari. Una volta installato, sostituirà automaticamente la funzionalità di stampa dei moduli di reso standard di PrestaShop.

## Personalizzazione

Per personalizzare l'aspetto del PDF:

1. Modifica la classe `PdfOrderReturn` nella directory `src/Pdf/`
2. Puoi modificare colori, font, layout e contenuti secondo le tue esigenze

## Supporto

Per assistenza o richieste di personalizzazione, contattare:
- Email: maxx.palermo@gmail.com
- Sito web: https://www.maxpalermo.it

## Licenza

Questo modulo è distribuito sotto licenza Academic Free License 3.0
