# NSSUIVIPROD FOR [DOLIBARR ERP CRM](https://www.dolibarr.org)

## Informations

- Numéro du module : 172791
- Dernière mise à jour : 25/10/2026
- Éditeur : [NSINFO](https://www.ns-info.fr)
- Thème : Eldy Menu
- Licence : GPLv3
- Disponible sur : Windows - MacOS - Linux

### Version

- Version : 23.0.1
- PHP : 7.4 - 8.3
- Compatibilité : Dolibarr 20.0.0 - 24.0.x
- NSINFO Framework : 23.8.0

## Liens

- Support & Assistance : [Forum dolibarr.fr](https://dolibarr.fr) / Par mail à contact@ns-info.fr
- Support déclaration : [Ticket](https://support.ns-info.fr)
- Documentation : [Wiki](https://www.ns-info.fr/)
- Dépôt GitHub : [nsinfosarl/nssuiviprod](https://github.com/nsinfosarl/nssuiviprod)
- Nsinfo Framework : [Télécharger Nsinfo](https://www.dolistore.com/product.php?id=713)
- D'autres modules développés par NSINFO disponibles sur [Dolistore.com](https://www.dolistore.com/index.php?controller=search&orderby=position&orderway=desc&tag=&website=marketplace&search_query=NSINFO)

## Fonctionnalités

Nssuiviprod : Suivi des sites, sans aucune modification du Core.

- Sites des clients : un onglet « Suivi des sites » sur la fiche tiers liste les sites du client (type, URL avec leur version et leur situation, statut)
- Fiche site : tiers, type de site (Dolibarr, WordPress, autre… selon le dictionnaire), statut en service / arrêté, notes, documents et agenda ; derniers événements sur la fiche (ajout d'un événement lié au site et à son tiers) ; toutes les actions sur le site, ses URL et ses plugins enregistrées dans son agenda avec leur détail (champs modifiés, plugins importés, supprimés, reliés…) ; libellé, tiers et type dans le bandeau de tous les onglets, chacun modifiable par son crayon
- URL du site (une par instance : production, développement, sandbox…) : type (accès direct, administration, API…), libellé facultatif (onglets des plugins : icône de l'environnement, puis libellé), version installée (numéro x.y.z), situation, date du dernier contrôle (date du jour par défaut sur la fiche URL et sur la ligne d'ajout des URL), complément en texte avancé ; ordre par glisser-déposer
- Environnement de chaque URL (production, développement, sandbox) : icône de couleur en première colonne des listes d'URL, libellé en info-bulle
- Plugins installés sur chaque URL : produit ou service Dolibarr, ou nom libre (plugin WordPress non référencé), avec leur version ; un onglet par URL sur la fiche site, avec recherche, sélection (cases à gauche) et suppression en masse, description du module sous son nom, colonne Éditeur, tri par clic sur les en-têtes ; plugins supprimés avec leur URL ; fiche plugin en onglets
- Plugins en location ou non : icône de couleur en première colonne, bascule d'un clic sur la fiche site, actions de masse dans la liste des plugins
- Version en cours sur le Dolistore (vérification depuis la fiche site ou par action de masse), mise en avant quand elle est plus récente que la version installée ; identification Dolistore mémorisée et réutilisée pour tous les clients
- Liens vers le Dolistore (icône du Dolistore) et vers le site de l'éditeur de chaque plugin
- Import du fichier JSON du module « Liste des modules externes » (`nslistmodexterne`) au niveau des URL de la fiche site, en une opération : URL de l'instance retrouvée ou créée (type d'URL et environnement choisis), version installée = version exacte de Dolibarr du fichier, date du dernier contrôle du jour, puis plugins de l'URL ; fichier envoyé ou fichier de l'instance ; lien vers les réglages de ce module sur le Dolibarr de l'URL (avec bouton de copie) dans l'onglet de ses plugins ; mise à jour des plugins existants, plugins ajoutés ou absents signalés (suppression possible), association aux produits plugins (référence ou libellé), tous les champs du JSON enregistrés sur le plugin (éditeur, numéro, famille, activé, dépendances, fonctionnalités…) ; vérification des versions Dolistore et liaison aux produits lancées automatiquement après l'import si l'option des réglages est activée
- Catégorie des produits plugins (réglages) : seuls les produits de cette catégorie et de ses sous-catégories sont proposés comme plugins
- Association automatique des plugins aux produits plugins (bouton dans chaque onglet d'URL et à l'import) : par la référence Dolistore (produit « LOC_ » pour la location), sinon par la référence ou le libellé du produit
- Référentiel des versions : version actuelle de chaque type de site dans les réglages (x.y.z) ; la version installée d'une URL est un numéro, comparé à cette version actuelle
- Situation de chaque URL : à jour, mise à jour disponible, version non renseignée, ou pas de version actuelle pour son type de site
- Liste des URL filtrable par situation (« À mettre à jour ») ; listes des sites et des plugins installés (quels clients ont tel plugin)
- Accueil du module : URL des sites en service par situation, sites en service par type
- Menu réductible à ses icônes (entrée « Réduire le menu », framework NSINFO)
- Menu « Réglages » du module avec un droit dédié (« Accéder aux réglages du module ») : menu masqué sans ce droit
- Réglages : accès aux dictionnaires Types de site et Types d'URL, version actuelle de chaque type de site ; modèle de numérotation des sites (standard par défaut, ou avancé avec masque)
- Attributs supplémentaires sur les sites, les URL et les plugins (réglages : onglet « Attributs supplémentaires », un sous-onglet par objet)

## Traduction

- Français
- Anglais

## Installation

**Prérequis : le framework NSINFO (module « NS INFO ») doit être installé et activé, version 23.8.0 ou supérieure.**
Sans lui, l'activation du module est refusée avec un message d'erreur.

### Méthode :

- Depuis le menu "Déployer/Installer un module externe" de Dolibarr :
- Glisser l'archive ZIP 'module_nssuiviprod-X.Y.Z' et cliquer sur "ENVOYER FICHIER"
- Activer le module dans la liste des Modules/Applications installés
