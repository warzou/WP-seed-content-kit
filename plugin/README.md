# WP Seed Content Kit

WP Seed Content Kit est un plugin WordPress de contenus éditoriaux structurés et de présentations réutilisables.

Il fonctionne sans thème imposé, sans ACF obligatoire et sans dépendance à un constructeur de page particulier.

## Fonctionnalités actuelles

### Témoignages

- contenus structurés ;
- nom ou initiales ;
- texte ;
- photo ;
- date du témoignage ;
- Information complémentaire ;
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

### Annuaire - sortie publique 0.6.0-dev

Le module natif Annuaire fournit son CPT administratif prive, dix-neuf metas validees, autorisation explicite et garde de publication. L4 ajoute une Data API publique fermee, des Collections par IDs, [seed_directory], deux groupes automatiques, une carte native responsive et des Templates Content Kit.

Seuls les contacts valides et explicitement visibles sont publics. Il existe aucune page individuelle, archive, recherche, REST/AJAX, migration runtime ou adaptateur inter-plugin.

### Cards

Cards affiche les articles WordPress natifs sous forme de cartes. Il ne crée pas de type de contenu supplémentaire.

### Templates WP Seed

Les templates permettent de mettre en forme les Témoignages et les Citations avec :

- le contenu du template dans l'éditeur WordPress ;
- Gutenberg ou Spectra ;
- un layout Divi Library sélectionné comme source du rendu.

Le plugin ne fournit pas de module Divi personnalisé. Avec Divi, la mise en forme est créée dans Divi Library puis sélectionnée depuis le template WP Seed.

### Content Data API

La Content Data API fournit une représentation normalisée des Citations, des Témoignages et de leur média. Elle centralise la lecture des données sans produire de HTML et sans dépendre d'un constructeur de page.

### Collections V1

Collections V1 sélectionne des Témoignages publics ordonnés et une Citation quotidienne déterministe. Le shortcode Témoignages utilise cette API tout en conservant ses valeurs par défaut historiques. Le shortcode Citations garde son hasard historique et propose explicitement `mode="daily"`.

### Dynamic Data

Dynamic Data expose 13 champs normalisés à des intégrations de présentation. Le résolveur utilise un contenu explicite ou le contexte WordPress courant, applique les permissions de lecture et retourne des valeurs vides typées lorsque le contexte n'est pas compatible.

### Gutenberg Block Bindings

Un provider serveur permet de lier huit champs texte WP Seed à l'attribut `content` des blocs Paragraphe et Titre Core. L'interface éditeur native WP Seed n'est pas finalisée : aucun sélecteur dédié n'est annoncé dans Gutenberg.

### Divi 5 Dynamic Content expérimental

Sous Divi 5, le provider Dynamic Content enregistre quatre champs Citation (Texte, Auteur, Époque, Source) et cinq champs Témoignage (Texte, Nom, Information complémentaire, Date du témoignage, Photo). Leur sélection et leur persistance visuelles ont été validées sous Divi 5.9.0.

Ces sources dépendent du contenu courant ou du contexte d'une boucle. Elles complètent les Templates WP Seed et les layouts Divi Library ; elles ne les remplacent pas.

## Template Extension API

Le contrat public 1.0 permet à un plugin tiers d'enregistrer un module de Template et des placeholders typés, puis de rendre un `seed_template` publié par slug. Le contexte transmis est fermé, les erreurs sont typées et le fallback reste sous la responsabilité du plugin appelant.

Détection minimale :

```php
wp_seed_content_kit_supports('template_extension', '1.0');
```

Le contrat fonctionne sans Divi, ne crée aucun endpoint et ne lit aucune donnée métier tierce implicitement. Voir `docs/TEMPLATE-EXTENSION-API.md`.

## Shortcodes publics

```text
[seed_cards]
[seed_testimonials]
[seed_quotes]
[seed_directory]
```

Exemples :

```text
[seed_testimonials limit="0" orderby="display_order" order="asc"]
[seed_testimonials featured="only" limit="3" template="accueil"]
[seed_testimonials ids="12,18,27" template="accueil"]
[seed_quotes template="citations-accueil"]
[seed_quotes mode="daily" template="citation-du-jour"]
[seed_directory status="practicing" featured="all" template="annuaire-carte"]
```

Le détail des attributs et placeholders se trouve dans `docs/USAGE.md`.

## Compatibilité

WP Seed Content Kit est conçu pour fonctionner avec :

- WordPress 6.5 ou version ultérieure ;
- PHP 7.0 ou version ultérieure ;
- les thèmes classiques ;
- Gutenberg ;
- Spectra ;
- Astra ;
- Divi Library ;
- les zones acceptant les shortcodes WordPress.

Le plugin reste fonctionnel sans Divi. Le provider Dynamic Content nécessite Divi 5 ; il n'est pas chargé sous Divi 4.

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
- de sélecteur WP Seed finalisé dans l'éditeur Gutenberg ;
- d'intégration ACF obligatoire ;
- d'import ou de migration automatique ;
- de rendu public complet pour le module Annuaire ;
- de module fonctionnel Créations sonores.

Le provider Divi 5 Dynamic Content reste expérimental. L'aperçu de certaines images ou boucles peut être incomplet dans le Visual Builder, et le texte alternatif d'une photo n'est pas garanti dans tous les modules. Le rendu frontend a été validé sous Divi 5.9.0.

## Documentation

- `docs/USAGE.md`
- `docs/TESTING.md`
- `docs/UPDATES.md`
