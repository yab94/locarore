
analyse l'architecture de ce projet
- on essai de respecter au mieux la théorie du DDD.
- on essai de respecter au mieux la théorie de Clean Architecture.

On ne verra pas comme un problème les BindAdapter de la couche Application même si cela donne à Application une connaissance de son implémentation on l'accepte car ce n'est que pour la DI, son code repose bien sur des interface. Par contre tout autre exception n'est pas accepté en dehors de BindAdapter.

Dans l'idée:
- Domain est pur (pas de dépendance)
- Application n'importe que des interfaces (définies en tant que Port) ou du Domain
- Infrastructure ne comporte que des classes "pures" infra ou des implémentations de Port applicatifs (Adapter)
- Presentation ne comporte que des Controller et des classes "pures" presentation
- Les controllers ne peuvent importer QUE des UseCase Applicatif