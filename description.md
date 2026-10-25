# NsSuiviProd — Suivi des sites de vos clients

Fiche de présentation du module pour **Dolistore.com** (version française puis version anglaise).

---

# 🇫🇷 Version française

## Nom du module

**NsSuiviProd – Suivi des sites de vos clients : URL, versions, plugins et mises à jour**

## Description courte

Suivez les sites de vos clients (Dolibarr, WordPress…) depuis Dolibarr : URL de production, de développement et de sandbox, version installée, plugins avec leur version et leur version Dolistore. Repérez en un coup d'œil les sites à mettre à jour.

## Description longue

### Tous les sites de vos clients, au même endroit

Vous installez et maintenez des Dolibarr, des WordPress ou d'autres applications pour vos clients ? Quelle version tourne chez qui, sur quelle adresse, avec quels modules, et lesquels sont à mettre à jour ?

**NsSuiviProd** tient ce suivi dans Dolibarr : une fiche par site, rattachée au tiers du client, avec ses URL, ses versions et ses plugins.

### Les URL de chaque site

- Une ligne par instance : **production**, **développement** ou **sandbox**, chacune avec une **icône de couleur**.
- Type d'URL (accès direct, administration, API…), nom facultatif, **version installée**, **date du dernier contrôle** et complément en texte enrichi.
- Ordre des URL par **glisser-déposer**.

### Les versions et les mises à jour

- Un **référentiel des versions** par type de site : version supportée, obsolète ou avec une **faille de sécurité**, avec un commentaire.
- La **situation** de chaque URL est calculée : à jour, mise à jour disponible, version obsolète, faille de sécurité, version non renseignée.
- Un menu **« Sites à mettre à jour »** et un tableau de bord sur l'accueil du module.

### Les plugins installés

- Un **onglet par URL** sur la fiche du site, avec ses plugins : produit Dolibarr ou nom libre, version, **location ou achat**.
- **Import de la liste des modules externes** d'un Dolibarr, à partir du fichier produit par le module gratuit **NsListModExterne** : lien direct (avec bouton de copie) vers la page où le télécharger, plugins mis à jour, plugins ajoutés et plugins disparus signalés, puis versions Dolistore vérifiées et plugins reliés à vos produits **automatiquement**.
- Recherche, sélection et suppression en masse.

### Le Dolistore intégré

- Un bouton **« Vérifier les versions Dolistore »** affiche la version en vente de chaque module, en orange quand elle est plus récente que la version installée.
- Liens vers la page Dolistore du module et le site de son éditeur.
- Les modules identifiés sont mémorisés et reconnus ensuite chez **tous vos clients**.
- Les plugins sont reliés automatiquement à vos **produits** (vente ou location), pour la facturation.

### Un historique complet

- Chaque action sur un site, ses URL et ses plugins est **enregistrée dans l'agenda** du site, avec son détail : ancienne et nouvelle valeur, plugins importés, supprimés ou reliés.
- Les derniers événements sont affichés sur la fiche du site.
- Un onglet **« Suivi des sites »** sur la fiche du tiers liste ses sites, leurs URL et leur situation.

### Simple et sûr

- Droits séparés pour les sites, pour le référentiel des versions et pour les réglages du module.
- Compatible **multisociété**, attributs supplémentaires sur tous les objets.
- **Aucune modification du Core** de Dolibarr.

## Points forts

- ✅ Une fiche par site client, avec ses URL de production, de développement et de sandbox
- ✅ Version installée, situation et sites à mettre à jour en un coup d'œil
- ✅ Plugins par URL, importés depuis le fichier des modules externes du Dolibarr du client
- ✅ Version Dolistore de chaque module, mémorisée pour tous vos clients
- ✅ Plugins reliés à vos produits, en vente ou en location
- ✅ Historique complet dans l'agenda du site
- ✅ Sans modification du Core

## Compatibilité

- Dolibarr **20.0 à 24.0** (testé sur Dolibarr 23.0)
- PHP **7.4 à 8.3** (testé en PHP 8.3)
- **Prérequis : framework NSINFO (module « NSINFO ») version 23.8.0 ou supérieure**, installé et activé
- Import des plugins : module **NsListModExterne** installé sur le Dolibarr du client
- Langues : français, anglais

## Mots-clés

suivi des sites, sites clients, maintenance, infogérance, parc, URL, production, développement, sandbox, version, mise à jour, faille de sécurité, plugins, modules externes, Dolistore, WordPress, location, agenda, NSINFO

---

# 🇬🇧 English version

## Module name

**NsSuiviProd – Track your customers' sites: URLs, versions, plugins and updates**

## Short description

Track your customers' sites (Dolibarr, WordPress…) from Dolibarr: production, development and sandbox URLs, installed version, plugins with their version and their Dolistore version. Spot the sites to update at a glance.

## Long description

### All your customers' sites in one place

Do you install and maintain Dolibarr, WordPress or other applications for your customers? Which version runs where, on which address, with which modules, and which ones need an update?

**NsSuiviProd** keeps track of all this in Dolibarr: one card per site, linked to the customer's thirdparty, with its URLs, versions and plugins.

### The URLs of each site

- One line per instance: **production**, **development** or **sandbox**, each with a **colored icon**.
- Type of URL (direct access, administration, API…), optional name, **installed version**, **date of last check** and rich text complement.
- URL order by **drag and drop**.

### Versions and updates

- A **versions referential** per type of site: supported, obsolete or with a **security issue**, with a comment.
- The **situation** of each URL is computed: up to date, update available, obsolete version, security issue, version not set.
- A **"Sites to update"** menu and a dashboard on the home page of the module.

### Installed plugins

- One **tab per URL** on the site card, with its plugins: Dolibarr product or free name, version, **rented or bought**.
- **Import of the list of the external modules** of a Dolibarr, from the file produced by the free module **NsListModExterne**: direct link (with a copy button) to the page where it is downloaded, plugins updated, new and removed plugins reported, then Dolistore versions checked and plugins linked to your products **automatically**.
- Search, selection and mass deletion.

### The Dolistore built in

- A **"Check the Dolistore versions"** button shows the version on sale of each module, in orange when it is newer than the installed version.
- Links to the Dolistore page of the module and to the website of its publisher.
- Identified modules are remembered and then recognized for **all your customers**.
- Plugins are automatically linked to your **products** (sale or rental), for invoicing.

### A complete history

- Every action on a site, its URLs and its plugins is **recorded in the agenda** of the site, with its detail: old and new value, plugins imported, deleted or linked.
- The latest events are shown on the site card.
- A **"Site tracking"** tab on the thirdparty card lists its sites, their URLs and their situation.

### Simple and secure

- Separate permissions for the sites, the versions referential and the setup of the module.
- **Multicompany** compatible, extra fields on all objects.
- **No change to the Dolibarr Core**.

## Highlights

- ✅ One card per customer site, with its production, development and sandbox URLs
- ✅ Installed version, situation and sites to update at a glance
- ✅ Plugins per URL, imported from the external modules file of the customer's Dolibarr
- ✅ Dolistore version of each module, remembered for all your customers
- ✅ Plugins linked to your products, sold or rented
- ✅ Complete history in the agenda of the site
- ✅ No core modification

## Compatibility

- Dolibarr **20.0 to 24.0** (tested on Dolibarr 23.0)
- PHP **7.4 to 8.3** (tested with PHP 8.3)
- **Requirement: NSINFO framework ("NSINFO" module) version 23.8.0 or higher**, installed and enabled
- Plugin import: **NsListModExterne** module installed on the customer's Dolibarr
- Languages: French, English

## Keywords

site tracking, customer sites, maintenance, managed services, fleet, URL, production, development, sandbox, version, update, security issue, plugins, external modules, Dolistore, WordPress, rental, agenda, NSINFO
