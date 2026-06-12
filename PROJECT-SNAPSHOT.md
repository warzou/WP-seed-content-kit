# PROJECT SNAPSHOT - WP-seed-content-kit

Date : 11 juin 2026
Statut : projet initialise
Nature : framework editorial WordPress reutilisable

## 1. Identite

Nom :

```text
WP-seed-content-kit
```

Objectif :

```text
Fournir un socle modulaire pour contenus structures et affichages reutilisables dans WordPress.
```

## 2. Sites connus

Site laboratoire :

```text
https://avecguillaume.fr
```

Premier site pilote production :

```text
https://therapsycorporel.fr
```

## 3. Contraintes majeures

- ACF optionnel.
- Divi 4 compatible.
- Divi 5 compatible.
- Astra compatible.
- Spectra compatible.
- Gutenberg compatible.
- Shortcodes universels.
- CSS prefixe.
- Aucun theme impose.
- Plugin installable par ZIP.
- Pas de dependance externe obligatoire.

## 4. Coeur du projet

Le coeur du projet est double :

```text
contenus structures + affichages reutilisables
```

Les CPT sont des modules.

Les affichages sont une couche independante.

## 5. Arborescence initiale

```text
WP-seed-content-kit/
├── docs/
├── plugin/
├── examples/
├── screenshots/
├── VISION.md
├── PROJECT-SNAPSHOT.md
├── AI-CONTEXT.md
└── ARCHITECTURE-GUARDRAILS.md
```

## 6. Roles des dossiers

### docs/

Documentation longue, guides, decisions d'architecture, notes de version.

### plugin/

Futur code du plugin WordPress installable par ZIP.

Aucun code n'est migre a l'initialisation documentaire.

### examples/

Exemples de shortcodes, configurations, presets, contenus de demonstration.

### screenshots/

Captures d'ecran de reference : admin, rendu front, compatibilite builders.

## 7. Direction V1

V1 doit rester simple :

- modules petits ;
- shortcodes universels ;
- CSS scoped ;
- pas de dependance obligatoire ;
- ACF optionnel plus tard ;
- compatibilite builders avant integration profonde.

## 8. Risques principaux

- creer trop de CPT trop tot ;
- coupler les affichages a un seul site ;
- imposer ACF ;
- imposer un theme ;
- ajouter du CSS global ;
- casser Divi/Spectra/Gutenberg ;
- transformer le plugin en theme de fait.

## 9. Definition de succes

Le projet est sur la bonne voie si :

- le meme plugin peut etre installe sur plusieurs sites ;
- les contenus restent simples a administrer ;
- les affichages peuvent etre inseres par shortcode ;
- le CSS reste confine ;
- ACF ameliore l'experience sans etre obligatoire ;
- les modules CPT peuvent etre actives selon le besoin reel.
