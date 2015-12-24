Architecture
============

The CMS project attempts to encourage a solid architecture across projects using this CMS.
It is inspired by the concepts from [DDD][ddd] and [Onion Architecture][onion]

[ddd]: https://en.wikipedia.org/wiki/Domain-driven_design
[onion]: https://blog.8thlight.com/uncle-bob/2012/08/13/the-clean-architecture.html

The structure of an onion architecture based application can be summarised by the following image:
![Layer Diagram](http://blog.8thlight.com/assets/posts/2012-08-13-the-clean-architecture/CleanArchitecture-81565aba46f035911a5018e77a0f2d4e.jpg)

The direction of dependency is inwards, hence the entities at the core of the diagram, the core
of the application, the "domain model" does NOT depend on anything external, it is decoupled from the implementation
details of the application. They focus on the business rules and the goal of the application.

Around that are the services, they follow the use cases of the application, the can also contain
business logic that does not clearly belong to particular entity. Anything from an outer layer of
the architecture must only be accessed via an interface. For instance, if a service needs to save
an entity to the underlying data-store (most likely a db), it will have a dependency to a repository
interface where it call `save` without caring where it actually saves to.

The outermost layer of the onion is the infrastructure, the tools that required to be part of the application but
not a central concern. For instance, we need to save user input to some persistence backend, well a
repository that talks to a database could be implemented in this layer.

