# CHANGELOG NSSUIVIPROD FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## 23.0.7 - 10/11/2026
- Fiche produit / service : l'onglet « Installations sur les sites » n'apparaît plus sur aucune page d'un produit qui n'est dans aucun site (Fichiers joints, Notes, Événements/Agenda, Marges, statistiques, onglets des autres modules : ces pages n'ont ni le contexte productcard ni globalcard) : contexte « main » ajouté aux hooks du module, chargé sur toutes les pages (désactiver puis réactiver le module)

## 23.0.6 - 09/11/2026
- Import du JSON des modules externes : les changements de version sont signalés (message et événement du site : « N plugin(s) dont la version a changé : nom (ancienne → nouvelle) ») ; chaque plugin garde la version précédente et la date du changement (date du fichier JSON, sinon de l'import) : colonne « Date du changement de version » affichée par défaut dans la liste des plugins (colonne « Version précédente » au choix), et pastille ↑ / ↓ avec l'ancienne version à côté de la version (date au survol) ; nouvelles colonnes version_previous et version_date du plugin (ALTER dans sql/update_1.0.0.sql)
- Fiche d'un site, onglet « Plugins » : titre « Plugins installés » supprimé (inutile, l'onglet le dit déjà), les sous-onglets Synthèse / URL remontent en haut
- Fiche d'un site, onglet « Plugins », sur l'onglet d'une URL (production, développement, sandbox) : choix du fichier JSON de mise à jour ; les plugins de cette URL sont ajoutés ou mis à jour quelle que soit l'adresse indiquée dans le JSON (l'URL doit appartenir au site), puis vérification Dolistore et liaison aux produits comme pour l'import habituel
- Fiche d'un site, onglet « Plugins » : la page s'ouvre en haut (plus d'ancre qui faisait défiler jusqu'à la liste des plugins), après un clic sur l'onglet, sur un sous-onglet ou après une action
- Réglages : avertissement en haut de page quand un réglage n'est pas à sa valeur par défaut (lien ↺ pour tout remettre par défaut)

## 23.0.5 - 09/11/2026
- Réglages, sous-onglet « Synthèse » : liste des modules non listés dans la synthèse des plugins d'un site (choix multiple, enregistré au changement ; le champ prend toute la largeur et s'agrandit sur plusieurs lignes selon le nombre de modules choisis) ; la synthèse les masque et une case « Afficher les n module(s) ignoré(s) » permet de les voir quand même (en grisé) ; en-tête de chaque URL de la synthèse : l'adresse de l'URL s'affiche au survol
- Fiche d'un site : nouvel onglet « Plugins » (à côté de Site, Notes…) qui reprend la section des plugins installés ; il s'ouvre sur un sous-onglet « Synthèse » : un tableau des modules en lignes et des URL (instances) en colonnes avec la version installée, l'absence d'un module sur une URL signalée (badge « Absent »), les versions plus anciennes que celle installée ailleurs en orange, la colonne Dolistore (liens et dernière version, en orange si plus récente) et une case « Seulement les écarts » ; les onglets de chaque URL suivent
- Réglages : les sections sont rangées en sous-onglets verticaux (Dictionnaires, Plugins installés, Dolistore, Référentiel des versions, Modèles de numérotation) ; le dernier sous-onglet ouvert est mémorisé
- Réglages : valeur par défaut déclarée sur chaque option (plugins affichés sur les services : oui, sur les produits : non, vérification Dolistore automatique : oui, plusieurs vérifications par jour : non, adresse de l'API) ; le picto « Remettre par défaut » apparaît quand la valeur diffère (identiques à celles posées à l'activation)
- Onglet « Sauvegarde des paramètres » dans l'administration (page partagée nsinfo/admin/config_module.php, juste avant ChangeLog) : export des paramètres du module dans un fichier JSON et import sur un autre Dolibarr ; framework NSINFO 23.9.2 minimum exigé à l'activation

## 23.0.4 - 26/10/2026
- Création d'un site : le type de site passe avant le nom du site, qui n'est plus obligatoire (le formulaire le remplit avec le libellé du type choisi tant qu'il n'a pas été saisi ; vide, il reprend le libellé du type de site, même désactivé, à la création et à la modification)
- Situation d'une URL : « Mise à jour mineure disponible » (badge vert clair) quand la version installée est de la même version majeure que la version de référence (24 pour 24.0.2), « Mise à jour disponible » (orange) sinon ; filtre, tableau de bord et liste des sites
- Import du fichier JSON et listes de plugins : les modules désactivés (« enabled » : false) ne sont plus pris en compte

## 23.0.3 - 26/10/2026
- Fiche module : produit/service du module (modifiable directement par son crayon), donné à ses plugins sans produit sur tous les sites et utilisé en priorité à l'import et à la liaison automatique
- Fiche site : nom de chaque plugin relié à la fiche de son module

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
