# 🍽️ GastroQR

**Sistema di gestione bollettini per ristoranti con QR code e stampa etichette P-touch**

---

## 📖 Descrizione

GastroQR è un'applicazione web PHP sviluppata per il **modulo 133 della formazione ICT svizzera**. Permette ai cuochi di un ristorante di gestire facilmente i bollettini della merce in arrivo, generando automaticamente QR code e stampando etichette professionali.

### 🎯 Funzionalità Principali

- **📷 Upload bollettini** tramite camera smartphone
- **🔲 Generazione QR code** automatica per ogni prodotto
- **🖨️ Stampa etichette** testato su P-Touch 24mm ottimizzate
- **👥 Sistema multi-utente** con autenticazione
- **⚙️ Pannello amministrazione** per gestione utenti
- **📊 Dashboard statistiche** e monitoraggio scadenze
- **📱 Design responsive** mobile-friendly

---

### 🗄️ Database Schema

#### Tabella `utenti`
| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `id` | INT PRIMARY KEY | ID univoco utente |
| `username` | VARCHAR(50) UNIQUE | Nome utente per login |
| `password` | VARCHAR(255) | Password hashata (bcrypt) |
| `nome` | VARCHAR(100) | Nome completo utente |
| `ruolo` | ENUM('cuoco', 'admin') | Ruolo utente |
| `attivo` | TINYINT(1) | Stato attivazione account |
| `data_creazione` | DATETIME | Timestamp creazione |

#### Tabella `bollettini`
| Campo | Tipo | Descrizione |
|-------|------|-------------|
| `id` | INT PRIMARY KEY | ID univoco bollettino |
| `user_id` | INT FOREIGN KEY | Riferimento utente creatore |
| `nome_prodotto` | VARCHAR(200) | Nome del prodotto |
| `data_scadenza` | DATE | Data di scadenza prodotto |
| `nome_file` | VARCHAR(255) | Nome file immagine |
| `percorso_file` | VARCHAR(500) | Percorso completo file |
| `qr_data` | TEXT | URL contenuto nel QR code |
| `data_creazione` | DATETIME | Timestamp caricamento |

---

## 🚀 Installazione

### 📋 Requisiti

- **PHP** 7.4+ con estensioni:
  - `PDO` e `PDO_MySQL`
  - `GD` per gestione immagini
  - `cURL` o `allow_url_fopen` per API QR
- **MySQL** 5.7+ o **MariaDB** 10.2+
- **Webserver** (Apache/Nginx)
- **Stampante Brother P-touch** con nastri TZe da 24mm (opzionale)

### ⚡ Setup Rapido

1. **Clona/scarica** il progetto
```bash
git clone [repository-url] gastroqr
cd gastroqr
```

2. **Configura database** in `config.php`:
```php
$host = 'localhost';
$dbname = 'gastroqr';
$username = 'your_db_user';
$password = 'your_db_password';
```

3. **Crea database** eseguendo `setup_database.sql`:
```sql
-- Via phpMyAdmin o riga di comando
mysql -u root -p < setup_database.sql
```

4. **Imposta permessi** cartella uploads:
```bash
chmod 755 uploads/
chown www-data:www-data uploads/  # Su sistemi Linux
```

### 🔐 Utenti Demo

| Username | Password | Ruolo | Nome |
|----------|----------|-------|------|
| `iadonj` | `Abc123!` | Amministratore | Amministratore |
| `cuoco1` | `password` | Cuoco | Mario Rossi |
| `cuoco2` | `password` | Cuoco | Lucia Verdi |

---

## 💻 Utilizzo

### 👨‍🍳 Per i Cuochi

1. **Login** con credenziali fornite dall'admin
2. **Fotografa** il bollettino con smartphone
3. **Inserisci** nome prodotto e data scadenza
4. **Carica** - il sistema genera automaticamente il QR
5. **Stampa** l'etichetta P-touch da 24mm
6. **Applica** l'etichetta sul prodotto

### 🛡️ Per l'Amministratore

1. **Login** con account admin
2. **Gestisci utenti** dal pannello amministrazione:
   - Crea nuovi account cuoco
   - Attiva/disattiva utenti
   - Monitora statistiche utilizzo
3. **Visualizza** tutti i bollettini del ristorante
4. **Controlla** prodotti in scadenza

### 📱 Scansione QR

1. **Scansiona** il QR code con qualsiasi app
2. **Visualizza** automaticamente:
   - Foto del bollettino originale
   - Nome prodotto e data scadenza
   - Chi ha caricato il bollettino
   - Alert se prodotto scaduto

---

## 🖨️ Stampa Etichette

### 🏷️ Formati Supportati

- **Nastri Brother TZe da 24mm**:
  - TZe-251 (nero su bianco) - Raccomandato
  - TZe-S251 (nero su bianco extra-adesivo)
  - TZe-FX251 (nero su bianco flessibile)

### ⚙️ Impostazioni Stampante

```
Formato: Personalizzato
Larghezza: 80mm
Altezza: 24mm
Orientamento: Orizzontale/Landscape
Margini: 0mm tutti i lati
Scala: 100% (NO adatta alla pagina)
```

### 📐 Layout Etichetta

```
┌─────────────────────────────────────────────────────────┐
│ ████████  │                                           │
│ ██    ██  │        Nome Prodotto                      │
│ ██ QR ██  │        Data Scadenza                      │
│ ██    ██  │                                           │
│ ████████  │                                           │
└─────────────────────────────────────────────────────────┘
   18x18mm                 ~55mm testo
```

**🍽️ Buon appetito con GastroQR!** 👨‍🍳✨

ideato e sviluppato da joshua iadonisi
LLM utilizzati nel webdesign e il troubleshooting: Anthropic Sonnet 4, OpenAI o4-mini-high
