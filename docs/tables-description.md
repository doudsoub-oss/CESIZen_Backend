# Documentation des tables — CESIZen

---

## `users`

### Rôle

Table centrale de l'application. Elle stocke tous les comptes utilisateurs, qu'ils soient simples utilisateurs, administrateurs ou super-administrateurs.

### Champs clés

- `id` → identifiant unique de l'utilisateur
- `name` / `email` → informations d'identification
- `password` → mot de passe hashé (jamais stocké en clair)
- `role` → niveau d'accès : `user`, `admin`, `super_admin`
- `is_active` → permet de désactiver un compte sans le supprimer
- `two_factor_secret` / `two_factor_recovery_codes` → support de l'authentification à deux facteurs
- `email_verified_at` → date de vérification de l'adresse email

### Utilité

Le cahier des charges (section 5.1) exige une gestion fine des rôles et des droits. Cette table est le point d'entrée de toute la gestion des accès : un visiteur anonyme n'a pas de ligne ici, un utilisateur connecté en a une avec `role = user`, et les administrateurs disposent de permissions étendues selon leur rôle.

---

## `audit_logs`

### Rôle

Table de traçabilité qui enregistre toutes les actions sensibles effectuées dans l'application. Elle constitue le journal d'audit de CESIZen.

### Champs clés

- `user_id` → qui a effectué l'action
- `action` → nature de l'action (ex : "create", "update", "delete")
- `auditable_type` / `auditable_id` → sur quelle entité l'action a été effectuée (ex : un utilisateur, un contenu)
- `old_values` / `new_values` → état de la donnée avant et après modification, stockés en JSON
- `ip_address` / `user_agent` → informations sur l'origine de la requête
- `created_at` → horodatage de l'action

### Utilité

Le cahier des charges (section 7.3) exige la journalisation des actions sensibles pour garantir la traçabilité et détecter tout comportement anormal. Cette table est également indispensable à la conformité RGPD, l'application traitant des données de santé mentale particulièrement sensibles.

---

## `categories`

### Rôle

Table d'organisation des contenus. Elle permet de classer les pages et articles dans une arborescence hiérarchique de catégories.

### Champs clés

- `name` / `slug` → nom et identifiant URL de la catégorie
- `description` → texte explicatif de la catégorie
- `parent_id` → référence vers une catégorie parente (permet l'imbrication)
- `position` → ordre d'affichage parmi les catégories sœurs
- `is_active` → permet de masquer une catégorie sans la supprimer

### Utilité

Le cahier des charges (section 5.2) demande que les contenus soient organisés de façon structurée. Grâce au champ `parent_id` auto-référencé, les administrateurs peuvent créer des catégories et sous-catégories (ex : "Santé mentale" > "Anxiété" > "Techniques de relaxation") sans modifier le code.

---

## `contents`

### Rôle

Table principale du module d'informations. Elle stocke l'ensemble des contenus éditoriaux de l'application : pages statiques, articles et ressources.

### Champs clés

- `title` / `slug` → titre et identifiant URL du contenu
- `excerpt` → résumé court, utile pour les listes et aperçus
- `body` → corps complet du contenu (format longtext)
- `type` → nature du contenu : `page`, `article`, ou `resource`
- `is_published` / `published_at` → gestion de la visibilité et de la date de publication
- `category_id` → rattachement à une catégorie
- `created_by` → identifiant de l'administrateur ayant créé le contenu

### Utilité

Le cahier des charges (section 5.2) exige que les administrateurs puissent créer, modifier et supprimer des contenus sans interruption de service. Cette table centralise toute la base éditoriale de l'application, consultable librement par les visiteurs et les utilisateurs connectés.

---

## `menus`

### Rôle

Table conteneur qui représente un groupe de navigation. Chaque menu correspond à un emplacement précis de l'interface.

### Champs clés

- `name` → nom du menu (ex : "Menu principal")
- `location` → emplacement dans l'interface : `main` (header), `footer`, ou `sidebar`

### Utilité

Le cahier des charges (section 5.2) demande que les administrateurs puissent gérer la structure de navigation de l'application. Cette table définit les zones de navigation disponibles, auxquelles sont ensuite rattachés les items via la table `menu_items`.

---

## `menu_items`

### Rôle

Table des entrées de navigation. Chaque ligne représente un lien visible dans un menu de l'application.

### Champs clés

- `menu_id` → menu auquel appartient l'entrée
- `parent_id` → permet de créer des sous-menus imbriqués
- `title` → texte affiché dans le menu
- `url` → lien de destination
- `content_id` → lien optionnel vers un contenu de la table `contents`
- `position` → ordre d'affichage dans le menu
- `is_active` → permet de masquer une entrée sans la supprimer

### Utilité

Grâce à cette table, les administrateurs peuvent depuis l'interface ajouter ou supprimer des liens, réorganiser leur ordre, créer des sous-menus et lier directement un item à une page de contenu existante — sans aucune modification du code source.

---

## `questionnaires`

### Rôle

Table de configuration des questionnaires de diagnostic. Elle définit les questionnaires disponibles dans l'application (ex : l'échelle de stress perçu PSS).

### Champs clés

- `title` / `slug` → nom et identifiant URL du questionnaire
- `description` / `instructions` → textes affichés à l'utilisateur avant de commencer
- `is_active` → permet de publier ou masquer un questionnaire
- `created_by` → administrateur ayant créé le questionnaire

### Utilité

Le cahier des charges (section 5.3) exige que les administrateurs puissent configurer les questionnaires de diagnostic. Cette table est le point d'entrée du module diagnostic : sans questionnaire actif, aucun diagnostic ne peut être réalisé.

---

## `questions`

### Rôle

Table des questions composant un questionnaire. Chaque ligne est une question posée à l'utilisateur lors d'un diagnostic.

### Champs clés

- `questionnaire_id` → questionnaire auquel appartient la question
- `text` → énoncé de la question
- `description` → précision ou contexte optionnel
- `position` → ordre d'affichage dans le questionnaire
- `is_required` → indique si une réponse est obligatoire

### Utilité

Cette table permet de décomposer un questionnaire en autant de questions que nécessaire, dans un ordre défini. Les administrateurs peuvent ajouter, modifier ou supprimer des questions sans recréer le questionnaire entier.

---

## `answer_options`

### Rôle

Table des options de réponse associées à chaque question. Chaque ligne est une réponse possible parmi laquelle l'utilisateur doit choisir.

### Champs clés

- `question_id` → question à laquelle cette option appartient
- `label` → texte de l'option affiché à l'utilisateur (ex : "Jamais", "Parfois", "Souvent")
- `score` → valeur numérique attribuée à cette réponse, utilisée pour calculer le score total
- `position` → ordre d'affichage des options

### Utilité

Le cahier des charges (section 5.3) exige un calcul automatique du score de stress. C'est grâce au champ `score` de cette table que chaque réponse choisie contribue au score final du diagnostic.

---

## `result_interpretations`

### Rôle

Table de configuration des résultats. Elle définit les plages de scores et leur signification, affichées à l'utilisateur à la fin d'un diagnostic.

### Champs clés

- `questionnaire_id` → questionnaire auquel s'applique cette interprétation
- `min_score` / `max_score` → plage de scores couverte
- `title` → intitulé du résultat (ex : "Stress faible", "Stress élevé")
- `description` → explication détaillée du résultat
- `recommendations` → conseils personnalisés affichés à l'utilisateur
- `color` → couleur associée au résultat (utile pour l'affichage visuel)

### Utilité

Le cahier des charges (section 5.3) exige que les administrateurs puissent configurer les pages de résultats. Cette table leur permet de définir librement les seuils et les messages associés, sans modifier le code, pour adapter les résultats à chaque questionnaire.

---

## `diagnostics`

### Rôle

Table des diagnostics complétés. Chaque ligne représente une session de diagnostic réalisée par un utilisateur connecté.

### Champs clés

- `user_id` → utilisateur ayant réalisé le diagnostic
- `questionnaire_id` → questionnaire utilisé
- `score_total` → score final calculé à partir des réponses
- `result_interpretation_id` → interprétation du résultat correspondant à la plage de score
- `completed_at` → date et heure de complétion

### Utilité

Le cahier des charges (section 5.3) exige l'historisation des résultats pour les utilisateurs connectés. Cette table constitue cet historique : un utilisateur peut retrouver tous ses diagnostics passés et suivre l'évolution de son niveau de stress dans le temps.

---

## `diagnostic_responses`

### Rôle

Table de détail des réponses données lors d'un diagnostic. Elle enregistre chaque réponse individuelle de l'utilisateur pour chaque question d'un diagnostic.

### Champs clés

- `diagnostic_id` → diagnostic auquel appartient cette réponse
- `question_id` → question à laquelle l'utilisateur a répondu
- `answer_option_id` → option choisie par l'utilisateur
- `score` → score de l'option au moment de la réponse (copie pour éviter les incohérences si l'option est modifiée ultérieurement)

### Utilité

Cette table assure la traçabilité complète de chaque diagnostic : on sait précisément quelle réponse a été donnée à chaque question. Le `score_total` de la table `diagnostics` est calculé en faisant la somme des `score` de toutes les lignes associées au diagnostic.