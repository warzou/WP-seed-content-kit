# WP Seed Content Kit

WP Seed Content Kit est un plugin WordPress de contenus éditoriaux structurés et de présentations réutilisables.

Il fonctionne sans thème imposé, sans ACF obligatoire et sans dépendance à un constructeur de page particulier.

## Fonctionnalités actuelles

### Témoignages

- contenus structurés ;
- nom ou initiales ;
- texte ;
- photo ;
- mise en avant ;
- ordre manuel ;
- templates réutilisables.

### Citations

- citation ;
- auteur facultatif ;
- époque ou date affichée facultative ;
- source ou contexte facultatif ;
- mise en avant ;
- ordre manuel ;
- templates réutilisables.

### Cards

Cards affiche les articles WordPress natifs sous forme de cartes. Il ne crée pas de type de contenu supplémentaire.

### Templates WP Seed

Les templates permettent de mettre en forme les Témoignages et les Citations avec :

- le contenu du template dans l'éditeur WordPress ;
- Gutenberg ou Spectra ;
- un layout Divi Library sélectionné comme source du rendu.

Le plugin ne fournit pas de module Divi personnalisé. Avec Divi, la mise en forme est créée dans Divi Library puis sélectionnée depuis le template WP Seed.

## Shortcodes publics

```text
[seed_cards]
[seed_testimonials]
[seed_quotes]
```

Exemples avec templates :

```text
[seed_testimonials template="accueil"]
[seed_quotes template="citations-accueil"]
```

Le détail des attributs et placeholders se trouve dans `docs/USAGE.md`.

## Compatibilité

WP Seed Content Kit est conçu pour fonctionner avec :

- WordPress et les thèmes classiques ;
- Gutenberg ;
- Spectra ;
- Astra ;
- Divi Library ;
- les zones acceptant les shortcodes WordPress.

ACF, Composer, npm et les services externes ne sont pas requis pour utiliser le plugin.

## Installation ZIP

Le ZIP doit contenir un seul dossier racine :

```text
wp-seed-content-kit/
```

Structure minimale attendue :

```text
wp-seed-content-kit/
- wp-seed-content-kit.php
- includes/
- assets/
- README.md
- docs/
```

Installation :

1. ouvrir Extensions > Ajouter une extension dans WordPress ;
2. téléverser le ZIP ;
3. activer WP Seed Content Kit ;
4. vérifier les modules actifs ;
5. tester les shortcodes sur une page de brouillon.

## Mises à jour

Les versions stables sont distribuées par GitHub Releases et détectées dans l'administration WordPress par le mécanisme de mise à jour embarqué.

L'asset de release attendu est `wp-seed-content-kit.zip`.

## Limites actuelles

Le plugin ne fournit pas :

- de module Divi personnalisé ;
- de widget Elementor ;
- de bloc Gutenberg personnalisé ;
- d'intégration ACF obligatoire ;
- d'import ou de migration automatique ;
- de modules fonctionnels Annuaire ou Créations sonores.

## Documentation

- `docs/USAGE.md`
- `docs/TESTING.md`
- `docs/UPDATES.md`
