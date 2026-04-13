# Boligkalkulator - WordPress Plugin

En profesjonell boligkalkulator plugin for WordPress med backend-innstillinger og embed-funksjonalitet.

## Beskrivelse

Boligkalkulator er en WordPress plugin som gir deg mulighet til å legge til en interaktiv boligkalkulator på din nettside. Kalkulatoren beregner finansieringsfordelingen av boligkjøp basert på brukerdefinerte innstillinger.

## Funksjoner

### Frontend Kalkulator
- **Kjøpesum input**: Brukeren kan oppgi ønsket kjøpesum
- **Egenfinansiering slider**: Juster egenfinansieringen fra 1-20%
- **Finansieringsvisualisering**: Fargekodet stolpediagram som viser fordelingen
- **Budsjettsammenfattelse**: Detaljert oversikt over finansieringskomponentene
- **Responsive design**: Fungerer perfekt på desktop, tablet og mobil
- **Dynamisk oppdatering**: Alle verdier oppdateres i sanntid

### Backend Admin Panel
- **Innstillinger for investeringsandel**: Sett standard investeringsandel (%)
- **Innstillinger for bankfinansiering**: Sett standard bankfinansieringsandel (%)
- **Innstillinger for egen kapital**: Sett standard egen kapitalandel (%)
- **Valutainnstillinger**: Tilpass valutasymbol (kr, NOK, osv.)
- **Tallformattering**: Tilpass desimalseparator og tusenseparator
- **Live forhåndsvisning**: Se kalkulatoren i sanntid mens du endrer innstillinger

## Installasjon

1. Last ned plugin-mappen
2. Last opp til `/wp-content/plugins/boligkalkulator/`
3. Aktiver plugin via WordPress admin panel
4. Gå til Innstillinger → Boligkalkulator for å konfigurere

## Bruk

### Legge til kalkulator på side/artikkel

Bruk shortcoden:
```
[boligkalkulator]
```

Kopier og lim inn denne koden i editoren på siden eller artikkelen hvor du ønsker å vise kalkulatoren.

### Backend Innstillinger

1. Gå til **Innstillinger** → **Boligkalkulator**
2. Juster prosentandelene:
   - **Investeringsandel**: Hvor stor andel skal investeres (f.eks. 20%)
   - **Bankfinansiering**: Hvor stor andel finansieres av bank (f.eks. 70%)
   - **Egen kapital**: Din egenkapital (f.eks. 10%)
   - *Merk: Disse må summere til 100%*
3. Tilpass valuta og tallformattering etter behov
4. Lagre innstillinger

## Filstruktur

```
boligkalkulator/
├── boligkalkulator.php          # Hovedplugin-fil
├── includes/
│   ├── class-boligkalkulator.php # Hovedklasse
│   ├── admin-settings.php       # Admin innstillinger
│   └── shortcode.php            # Shortcode rendering
├── assets/
│   ├── css/
│   │   ├── frontend.css         # Frontend stilark
│   │   └── admin.css            # Admin stilark
│   └── js/
│       ├── frontend.js          # Frontend JavaScript
│       └── admin.js             # Admin JavaScript
└── README.md                    # Denne filen
```

## Tekniske detaljer

- **PHP Version**: 7.4+
- **WordPress Version**: 5.0+
- **Dependencies**: jQuery (inkludert i WordPress)

## API og Hooks

### Filters

`boligkalkulator_settings` - Filtrerer innstillinger før de brukes
`boligkalkulator_formatted_price` - Filtrerer formatert pris

### Actions

`boligkalkulator_before_calculator` - Kjøres før kalkulatoren rendres
`boligkalkulator_after_calculator` - Kjøres etter kalkulatoren rendres

## Konfigurasjonseksempel

```php
// functions.php
add_filter('boligkalkulator_settings', function($settings) {
    $settings['invest_percentage'] = 25;
    return $settings;
});
```

## Sikkerhet

- All brukerinput blir sanitisert
- Alle utdata blir escaped
- Nonces brukes for formularer (implementeres ved behov)
- Settings er sikret med capability check (`manage_options`)

## Utvikling og Justering

### Endre CSS

Rediger `assets/css/frontend.css` for kalkulatorstil og `assets/css/admin.css` for admin-panel.

### Endre JavaScript logikk

Rediger `assets/js/frontend.js` for kalkulatorlogikk og `assets/js/admin.js` for admin-panel.

### Legge til nye innstillinger

1. Legg til input-felt i `includes/admin-settings.php`
2. Legg til sanitiseringsfunksjon i `includes/class-boligkalkulator.php`
3. Bruk innstillingen i `assets/js/frontend.js`

## Troubleshooting

### Kalkulator vises ikke
- Sjekk at plugin er aktivert
- Sjekk at shortcoden `[boligkalkulator]` er riktig skrevet
- Cleere WordPress cache hvis du bruker cache-plugin

### Prosentandeler validerer ikke
- Sjekk at sum av alle prosentandeler = 100%
- Innstillinger må lagres før kalkulator oppdateres

### JavaScript-feil
- Åpne Developer Tools (F12) og sjekk konsoll
- Sjekk at jQuery er lastet
- Sjekk at plugin-filene er intakte

## Lisens

GPL v2 or later

## Forfatter

Robin Andersen - https://robin.as

## Support

For spørsmål eller problemer, kontakt Robin Andersen.
