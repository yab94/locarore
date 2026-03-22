Ce document est le contexte du projet, il n'engendre aucune action à la lecture, juste de la prise d'information de contexte, tu devras le lire à chaque fois et il sera accompagné de demandes explicites depuis le terminal. Ce sont ces demandes qui engendreront des actions.

Il est question ici de concevoir une application pour gérer la location de matériel de décoration.
Par exemple: des lettres géantes lumineuses, des vases, des arches, etc...


Vision métier
L'application sera composées de deux grandes parties

- La partie privée

Accessible via un mot de passe simple qui sera en configuration (on fait simple pour le POC)
Possibilité de créer des catégories de produits
Possibilité de créer des produits appartenant à une ou plusieurs catégories
Possibilité d'activer/desactiver une catégorie facilement
Possibilité d'activer/desactiver un produit facilement
Un produit a un stock/quantité
Un calendrier des reservation est accessible
Un produit peut avoir des photos, il doit etre possible d'en uploader
Possibilité de voir et valider/refuser les reservations

Les demandes de reservation doivent intégrer l'adresse la personne, son emailn son nom son tel et l'eventuel adresse du lieu de l'evenement.

Gérer la notion de Pack:
Un pack est une composition de produit (un pack est composé de plusieurs produits en quantité fixe). Si un pack est reservé il faudt décrémenter les stock des produits le composant sur le sdates données.

Sur la fiche privée (admin) d'un produit on doit voir un calendrier (comme la page calendrier) mais filtrant uniquement sur ce produit.

Les slug catégorie et produit sont générées dynamiquement, mais doivent pouvoir être modifié par le user, en filtrant la saisie du user selon les mêmes contraintes SEO/URL bien sûr.

Les descriptions de produits et catégories doivent être stylisables et donc leur formulaire doit propser un wysiwyg.

l'url d'un produit ou d'une sous categorie doit comporter les troncons des categories parentes.

On peut revenir sur les status de commandes (si on se trompe par exemple, confirmation par erreur)

Il faut un nouveau status pour les reservaitons car si deux personnes louent la meme choses sur la même période et qu'un devis est envoyé achacun le premier qui répond invldera l'autre. Il faut dont un status intermédiaire qui bloque le stock comme le ferait 'confirmed' mais sans pour autant apparaitre en vert dans le calendrier, il faudra apparaitre en orange en status 'quoted' devis en cours.

- La partie publique

Une navigation catégorie/produits optimisées pour le SEO, des urls slug etc...
Possibilité de créer un panier ou le client reserve X produits à X dates pour une durée
En gros un internaute peut reserver un produit en une quantité donnée pour un interval de date donnée
La fiche produit montrera un carousel des photos du produit, la description, etc...
Les catégories permettront une navigation arborescente avec des aperçu de produits

Lors de la navigation/reservation il va falloir gérer le fait que le client peut resevrer plusieurs produit par panier mais qu'un panier ne concerne qu'un interval de date, du coup il va falloir trouver le meilleur moyen pour que cela soit fluide dans la compréhension client.

La reservation aura toujours une granularité à la journée on ne gère pas les heures.

La validation du panier créer une demande de réservation, cette demande doit ensuite etre validée en partie privée pour être vraiement prise en compte et impacter les stocks sur les dates concernée

Vision technique

L'application sera sur un hebergement perso OVH, c'est la raison pour laquelle on va privilégier les technologies PHP8.5 et MySQL.

Pas de framework, fais du code simple, il y aura un répertoire lib ou on pourra faire de petits tools mais on va minimiser au possible.

Config:
- Application configurable avec un fichier INI

Pour le SEO et tout on va gérer avec un .htaccess qui redirigera sur un index.php avec un mini router derriere.

Pas d'ORM ou autres on est OK avec le fait de dev en couple avec MySQL.

Structure bien le code en DDD dés le départ.

Pour le POC on ne gère pas le design, fais un design simpliste 

Prévois une dockerisation avec un docker-compose.yml (service: nginx, php-fpm, mysql, ...)

Namespace du projet Rore

Crée un Makefile avec les commande build/start/stop

Utilise Tailwind pour les vues


Politique sur les prix


- Vendredi au lundi (inclus un WE)
- D'une date A a une date B (plusieurs jours > 3)
- Professionnel qui loue pour une seule soirée mais bloque X jours
