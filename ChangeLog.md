# CHANGELOG NSSUIVIPROD FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## 23.0.2 - 25/10/2026
- Modules uniques : table des modules (identité et informations Dolistore une seule fois), plugins des URL reliés à leur module ; menu, liste et fiche « Modules » ; migration automatique des plugins existants, lancée une seule fois
- Dolistore : un module interrogé au plus une fois par jour (réglage pour en autoriser plusieurs, boutons toujours actifs) ; tâche planifiée quotidienne ; adresse de l'API réglable (corrige « Le Dolistore ne répond pas » en Dolibarr 20)
- Services et produits : onglet et colonne « Installations sur les sites » (clic vers le site), un réglage pour les services (activé) et un pour les produits (désactivé)
- Liste des sites : colonnes URL et Version installée (couleur selon la version actuelle)
- Tableau des plugins de la fiche site : colonnes à ajouter parmi les champs du plugin et du module
- Pages rangées dans core/site, core/url, core/plugin, core/module ; avertissement si la base n'est pas à jour (désactiver puis réactiver le module, voir le README)

## 23.0.1 - 25/10/2026
- Import du fichier JSON au niveau des URL, en une opération : URL créée ou mise à jour (version exacte, date du contrôle), plugins ajoutés ou mis à jour, vérification Dolistore ensuite (option)
- Version actuelle de chaque type de site dans les réglages (x.y.z), à la place du référentiel des versions
- Suppression d'une URL avec ses plugins
- Tableau des plugins : cases à gauche, description, colonne Éditeur, tri des colonnes, largeurs adaptées

## 23.0.0 - 24/10/2026
- Version initiale « Suivi des sites » (Site tracking), module NS INFO n° 172791 construit avec ModuleBuilder ; Dolibarr 20 à 24, PHP 7.4 à 8.3, framework NSINFO 23.8.0 minimum (backport des fonctions de Dolibarr 23, vérifié à l'activation) ; multisociété, attributs supplémentaires sur tous les objets
- Sites des clients : référence automatique (modèles standard ou avancé), tiers, type de site, statut en service / arrêté, description (affichée seulement si elle est remplie) ; bandeau de tous les onglets avec le nom, le tiers et le type, modifiables par leur crayon ; derniers événements sur la fiche, nombre d'événements dans le titre de la liste de l'onglet Événements
- URL de chaque site (une par instance) : environnement production / développement / sandbox (icône de couleur), type, libellé facultatif (onglets des plugins : icône de l'environnement puis libellé), version installée (bouton « + » pour créer une version), situation, date du dernier contrôle (date du jour par défaut), complément ; ordre par glisser-déposer
- Référentiel des versions par type de site (supportée, obsolète, faille de sécurité) ; situation de chaque URL, dernière version dans les listes, filtre et menu « Sites à mettre à jour » (URL à mettre à jour) et tableau de bord sur l'accueil
- Plugins par URL (un onglet par URL sur la fiche site) : produit de la catégorie « plugins » (et sous-catégories) ou nom libre, version, location (icône, bascule d'un clic, actions de masse) ; recherche, sélection et suppression en masse ; fiche plugin en onglets
- Import des plugins depuis le fichier JSON du module « Liste des modules externes » (nslistmodexterne), avec le lien vers ses réglages sur le Dolibarr de l'URL (bouton de copie rapide) : mise à jour des plugins existants, tous les champs du JSON, plugins ajoutés et absents signalés (suppression possible des absents), puis vérification des versions Dolistore et liaison aux produits lancées automatiquement
- Dolistore : version en cours (mise en avant si plus récente), liens vers le Dolistore et le site de l'éditeur, identification mémorisée et réutilisée pour tous les clients ; liaison automatique des plugins aux produits (référence Dolistore, LOC_ pour la location, ou libellé)
- Agenda : toutes les actions sur le site, ses URL et ses plugins enregistrées avec leur détail (champs modifiés, ordre des URL, import, vérification Dolistore, liaison aux produits, suppressions groupées)
- Onglet « Suivi des sites » sur la fiche tiers (visible aussi des utilisateurs externes pour leur tiers) ; dictionnaires Types de site et Types d'URL ; droits sur les sites, les versions et les réglages du module ; pictos de couleur (menus, objets, réglages) ; menu réductible
