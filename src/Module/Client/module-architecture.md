## Loosely Coupled Modules
Modules should be loosely coupled, meaning that they should not or only slightly dependent on each other.
Whenever possible, modules should be completely independent and not share any component 
as this has many advantages. It's mainly easier to refactor and maintain in the long run as changes
can be made without worrying about affecting other modules.

### When Modules share Elements
If multiple modules need an element from one Module, there are two options, and 
the one chosen depends on the use case. 
1. The element is extracted into a separate module folder on the same level as the other modules.
   * An example for this is Authorization, which contains Enum, Service, Exception and Repository 
used in multiple modules and thus has its own Module folder.
1. Keep the element in the Module it belongs to and use it in the other modules. This makes the 
modules that use this element dependent on the module that stores it.
   * An example for this would be the UserRole and UserStatus Enum. 
     The Multiple Modules use the UserRole and UserStatus 
     Both are stored in the User Module.

I'd say the main factors that influence such a choice are
1. How many elements are concerned, is it only an Enum or a whole feature with logic and database access?
2. Can the element be clearly tied to one Module, or is it not very specific to one Module? 

Extracting the shared elements into a separate Module folder is probably always better in terms of
SOLID principles, but it has the disadvantage of bloating `src/Module` with many folders.  
This is why I find it acceptable to make the compromise of keeping the shared elements in the Module they belong 
to, as long as it's only a few elements and not an entire feature.

## Features inside a Module
The modules contain multiple features which each has their own Application, Domain and Infrastructure layer
as per the [vertical slice architecture]().
((The `Data` folder contains the DTOs and is layer-independent.  
It is stored in the Module folder if it's used by different features or in the feature folder if it belongs
to a specific feature.))

Features should also mainly be designed to be loosely coupled and independent of each other.  

### When Features share Elements
If multiple features share the same functionality (e.g. Validation), it should be extracted into a separate feature
folder on the same level as the other features. This is to have the feature only be responsible for 
one specific task and to align with the Single Responsibility Principle (SRP). 

I would say that the compromise which is acceptable for modules (keeping the shared elements in the Module 
they belong to) does not apply to features. Features should be as independent as possible. Either be 
designed to be used by other features or be not share anything. 

### Folder structure
The folder structure of a module is as follows:
```yml
├── {ModuleX}
│       │   ├── Data # DTOs
│       │   ├── Feature1
│       │   │   ├── Application # or short /Action if the Application layer only contains Action
│       │   │   ├── Domain # (or short /Service if the Domain only contains Service)
│       │   │   └── Infrastructure # (or short /Repository if the Infrastructure only contains Repository)
│       │   ├── Feature2
│       │   │   ├── Action
│       │   │   ├── Service
│       │   │   └── Repository
```

### Folder creation
Folders are generally only created if they bring value.   
If a feature slice only contains Service (or something else) from the domain layer, the Domain folder is omitted. 
The same goes for Repository (or else) in the Infrastructure layer and Action (or else) in the Application layer. 

This is to prevent unnecessary nesting and to keep the codebase as clean as possible but the developer
should be aware that the components belong to a different layer and that if another component of the same layer
is added, the folder should be created.