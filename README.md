# Vereinsverwaltung

Ein umfassendes WordPress-Plugin zur Verwaltung von Vereinssparten, Ansprechpartnern, Terminen, Klassen und Ergebnissen.

## Features

- **Sparten-Verwaltung**: Erstellen und verwalten Sie verschiedene Vereinssparten
- **Ansprechpartner**: Hinterlegung von Kontaktpersonen pro Sparte mit Funktionsbezeichnung
- **Termine**: Verwaltung von Terminen und Events mit Ort und Datum
- **Klassen**: Definition von Trainingsklassen oder Gruppen
- **Ergebnisse**: Erfassung und Darstellung von Sportergebnissen
- **Benutzerprofile**: Erweiterte Benutzerdaten (Telefon, Adresse, Sparte, Klasse)
- **Widgets**: Termine-Widget für die Seitenleiste
- **Shortcodes**: Mehrere Shortcodes zur Frontend-Darstellung
- **REST API**: Öffentliche API for programmgesteuerten Zugriff
- **Dashboard-Widget**: Schneller Zugriff auf kommende Termine

## Installation

1. Das Plugin in `/wp-content/plugins/wordpress_vereinsverwaltung/` ablegen
2. Im WordPress Admin-Panel unter "Plugins" aktivieren
3. Es erscheint ein neues Menü "Ergebnisse" und unter "Einstellungen" werden neue Optionen hinzugefügt

## Verwendung

### Admin-Panel

Das Plugin fügt folgende Seiten im WordPress Admin hinzu:

- **Ergebnisse** (Hauptmenü): Verwaltung von Sportergebnissen
- **Termine** (Hauptmenü): Verwaltung von Terminen und Events
- **Sparten** (Einstellungen → Sparten): Definition der Vereinssparten
- **Ansprechpartner** (Einstellungen → Ansprechpartner): Kontaktpersonen pro Sparte
- **Klassen** (Einstellungen → Klassen): Trainingsklassen oder Gruppen

### Shortcodes

#### [vv_ansprechpartner]
Zeigt Ansprechpartner einer Sparte als Grid an.

**Parameter:**
- `sparte` (erforderlich): Name oder ID der Sparte

**Beispiel:**
```
[vv_ansprechpartner sparte="Volleyball"]
```

#### [vv_termine_tabelle]
Zeigt eine Tabelle mit Terminen einer Sparte an.

**Parameter:**
- `sparte` (erforderlich): Name oder ID der Sparte
- `limit` (optional): Maximale Anzahl der angezeigten Termine (Standard: alle)

**Beispiel:**
```
[vv_termine_tabelle sparte="Fußball" limit="10"]
```

#### [vv_buehne]
Zeigt öffentliche Benutzerprofile einer Sparte an.

**Parameter:**
- `sparte` (erforderlich): Name oder ID der Sparte

**Beispiel:**
```
[vv_buehne sparte="Basketball"]
```

### Benutzerprofile erweitern

Im Benutzer-Bearbeitungsbereich (Profil) stehen zusätzliche Felder zur Verfügung:

- **Telefon**: Telefonnummer des Benutzers
- **Adresse**: Adressangaben
- **Sparte**: Zuordnung zu einer Vereinssparte
- **Klasse**: Zuordnung zu einer Trainingsklasse
- **Öffentliches Profil**: Profil im Frontend sichtbar machen (für vv_buehne)
- **Banner**: Bannerbild für das öffentliche Profil

## REST API

Die REST API bietet programmierten Zugriff auf Ansprechpartner und Sparten.

### Basis URL
```
/wp-json/vereinsverwaltung/v1/
```

### Endpoints

#### GET /ansprechpartner
Gibt alle Ansprechpartner aller Sparten zurück.

**Beispiel:**
```bash
curl https://example.com/wp-json/vereinsverwaltung/v1/ansprechpartner
```

**Response:**
```json
[
  {
    "name": "Max Mustermann",
    "funktion": "Vorsitzender",
    "email": "max@example.com",
    "phone": "+49 1234 567890",
    "address": "Klosterstr. 1\n12345 Berlin",
    "sparte_id": "sparte-001",
    "sparte_name": "Volleyball",
    "avatar_url": "https://www.gravatar.com/avatar/..."
  }
]
```

#### GET /ansprechpartner/{sparte}
Gibt alle Ansprechpartner einer spezifischen Sparte zurück.

**Parameter:**
- `sparte`: Sparten-ID oder Sparten-Name (wird aufgelöst)

**Beispiele:**
```bash
# Nach Sparten-Name
curl https://example.com/wp-json/vereinsverwaltung/v1/ansprechpartner/Volleyball

# Nach Sparten-ID
curl https://example.com/wp-json/vereinsverwaltung/v1/ansprechpartner/sparte-001
```

**Response:**
```json
[
  {
    "name": "Anna Schmidt",
    "funktion": "Trainerin",
    "email": "anna@example.com",
    "phone": "+49 9876 543210",
    "address": "Hauptstr. 42\n10115 Berlin",
    "sparte_id": "sparte-001",
    "sparte_name": "Volleyball",
    "avatar_url": "https://www.gravatar.com/avatar/..."
  }
]
```

#### GET /sparten
Gibt eine Liste aller verfügbaren Sparten zurück.

**Beispiel:**
```bash
curl https://example.com/wp-json/vereinsverwaltung/v1/sparten
```

**Response:**
```json
[
  {
    "id": "sparte-001",
    "name": "Volleyball"
  },
  {
    "id": "sparte-002",
    "name": "Fußball"
  },
  {
    "id": "sparte-003",
    "name": "Basketball"
  }
]
```

### Response Format

Jeder Ansprechpartner enthält folgende Felder:

| Feld | Typ | Beschreibung |
|------|-----|-------------|
| name | string | Anzeigename des Ansprechpartners |
| funktion | string | Funktionsbezeichnung (z.B. "Vorsitzender", "Trainer") |
| email | string | E-Mail-Adresse |
| phone | string | Telefonnummer |
| address | string | Postadresse (mehrzeilig) |
| sparte_id | string | ID der Sparte |
| sparte_name | string | Name der Sparte |
| avatar_url | string | Absolute URL zum Profilbild (Gravatar) |

## Seiten-Zuordnung zu Sparten

Seiten können einer bestimmten Sparte zugeordnet werden:

1. Seite bearbeiten
2. Im Meta-Box "Vereinsverwaltung" die Sparte auswählen
3. Speichern

Widgets, die in der Seitenleiste "Sparte: [Name]" platziert werden, erscheinen automatisch nur auf Seiten mit dieser Zuordnung.

## Dashboard-Widget

Auf dem WordPress Dashboard wird ein Widget "Termine" angezeigt, das die kommenden Termine übersichtlich darstellt.

## Technische Details

### Konstanten

```php
Vereinsverwaltung_Plugin::OPT_SPARTEN          // Option: Sparten
Vereinsverwaltung_Plugin::OPT_FUNKTIONEN       // Option: Funktionen
Vereinsverwaltung_Plugin::OPT_ANSPRECHPARTNER  // Option: Ansprechpartner
Vereinsverwaltung_Plugin::OPT_TERMINE          // Option: Termine
Vereinsverwaltung_Plugin::OPT_KLASSEN          // Option: Klassen
Vereinsverwaltung_Plugin::OPT_ERGEBNISSE       // Option: Ergebnisse

Vereinsverwaltung_Plugin::META_PAGE_SPARTE            // Seiten-Meta: Sparten-Zuordnung
Vereinsverwaltung_Plugin::META_USER_PHONE            // User-Meta: Telefon
Vereinsverwaltung_Plugin::META_USER_ADDRESS          // User-Meta: Adresse
Vereinsverwaltung_Plugin::META_USER_SPARTE           // User-Meta: Sparten-Zuordnung
Vereinsverwaltung_Plugin::META_USER_KLASSE           // User-Meta: Klassen-Zuordnung
Vereinsverwaltung_Plugin::META_USER_PUBLIC_PROFILE   // User-Meta: Öffentliches Profil
Vereinsverwaltung_Plugin::META_USER_BANNER           // User-Meta: Banner-URL
Vereinsverwaltung_Plugin::META_USER_BANNER_ID        // User-Meta: Banner-Datei-ID
```

### Hooks

Das Plugin registriert folgende custom Hooks:

- `vv_sidebar_base_id`: Filterbar - Standard-Seitenleisten-ID für Sparten-Widgets
- Weitere Admin-Hooks für Datenverarbeitung (intern)

## Kompatibilität

- **WordPress**: 5.0+
- **PHP**: 7.4+
- **Datenspeicherung**: WordPress Options und User-Meta

## Datenspeicherung

Alle Daten werden in WordPress-Options und User-Metadaten gespeichert:

- Sparten, Ansprechpartner, Termine, Ergebnisse: `wp_options`
- Benutzer-Erweiterungen: `wp_usermeta`

## Support

Für Probleme oder Fragen bitte ein Issue im Plugin-Repository erstellen.

## Lizenz

Siehe LICENSE-Datei im Plugin-Verzeichnis.

---

**Version**: 1.0.1  
**Autor**: Henrik Hansen
