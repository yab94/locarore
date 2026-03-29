

Regles d'architecture du projet

Objectif general:
- respecter au mieux DDD et Clean Architecture, avec quelques ecarts explicites et assumes.

Regles de couches:
- Domain:
	- ne depend pas de Application, Infrastructure ni Presentation.
	- exception assumee: l'usage de RRB\Type\Castable (et Typable si necessaire) est autorise dans Domain.
	- cette exception est consideree comme un utilitaire technique interne, pas comme une fuite de logique applicative.
- Application:
	- depend uniquement de Domain, de ses propres abstractions (Ports) et du framework technique de base (RRB) quand c'est necessaire.
	- les UseCase doivent injecter des interfaces (Ports) plutot que des implementations concretes.
- Infrastructure:
	- contient les implementations techniques et les Adapters des Ports applicatifs.
	- peut dependre de Application (pour implementer les Ports), de Domain et de RRB.
- Presentation:
	- contient les Controllers et les classes de presentation.
	- les Controllers doivent orchestrer des UseCase applicatifs.
	- les dependances techniques de presentation (base controller, seo, html, csrf, etc.) sont autorisees.

Regle DI:
- toute la configuration DI doit vivre hors code metier (composition root), pas dans les classes source.
- les attributs de binding dans le code source sont interdits.
- bind() est utilise pour les mappings globaux (interface -> implementation).
- bind() est utilise pour les cas contextuels/scalaires (configuration par parametre).

Positionnement par rapport a la theorie "pure":
- ecart assume 1: Domain peut utiliser Castable/Typable de RRB.
- ecart assume 2: Presentation n'est pas limitee a "UseCase uniquement" en constructeur; les helpers presentation/framework sont acceptes.
- ecart assume 3: Application peut contenir des services applicatifs (pas uniquement des UseCase), tant qu'ils respectent les frontieres de dependances.

Principe de gouvernance:
- toute exception doit etre explicite, documentee et testable dans les tests d'architecture.

Peux-tu me donner ton avis sur l'archi ainsi que ton avis sur mes règles.
Indique-en quoi mes règles diffères des standards de la théorie pure.