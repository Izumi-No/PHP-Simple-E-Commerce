# Marketplace Entities

## User

Represents a user registered in the marketplace.

- id uuid
- name text
- nickname text
- shop? Shop.id

### Relationships

- User has zero or one Shop
- User has many UserIdentity
- User has many Session
- User has many Impression
- User has many CollectableList

---

## UserIdentity

Represents an authentication identity associated with a User.

An identity defines how a user authenticates with the marketplace.

- id uuid
- user User.id
- provider AuthenticationProvider
- provider_subject text
- email? string
- password_hash? string

### AuthenticationProvider

```text
Password
OpenIDConnect
```

For `Password` authentication:

- `password_hash` is required
- `provider_subject` is not required

For `OpenIDConnect` authentication:

- `provider` is `OpenIDConnect`
- `provider_subject` contains the OIDC `sub` claim
- `password_hash` is not required

The combination of `provider` and `provider_subject` must be unique.

### Relationships

- UserIdentity belongs to one User
- User can have multiple UserIdentity

This allows a User to authenticate using multiple providers.

Example:

```text
User
├── Password Identity
├── Google Identity
└── GitHub Identity
```

---

## Session

Represents an active authentication session.

- id uuid
- user User.id
- expiration datetime
- created_at datetime

A Session represents a specific login, while UserIdentity represents an authentication identity.

A User can have multiple active sessions.

Example:

```text
User
├── Session - Desktop
├── Session - Mobile
└── Session - Browser
```

The session identifier is stored by the client, preferably in a secure HTTP-only cookie.

---

## TempUser

Represents a temporary user registration that has not yet been completed.

- id uuid
- email string
- password_hash string
- expiration datetime

A TempUser can be used for flows such as email verification.

```text
TempUser
    │
    │ email verification
    ▼
User
```

The password must be stored as a password hash.

Expired TempUsers should no longer be usable for account creation.

---

# Shop

Represents a shop owned by a User.

- id uuid
- name string
- banner? url
- owner User.id
- collectables Collectable[]
- tags Tag[]

### Relationships

- Shop belongs to one User
- Shop has many Collectable
- Shop has many Tag

A User can own at most one Shop.

---

# Tag

Represents a tag used to categorize Collectables within a Shop.

- id uuid
- name string
- shop Shop.id
- collectables Collectable[]

### Relationships

- Tag belongs to one Shop
- Tag can belong to many Collectable
- Collectable can have many Tag

A tag name must be unique within its Shop.

---

# Collectable

Represents an item offered for sale by a Shop.

- id uuid
- name text
- description text
- images url[]
- price decimal
- tags Tag[]
- impressions Impression[]
- shop Shop.id

### Relationships

- Collectable belongs to one Shop
- Collectable has many Tag
- Collectable has many Impression
- Collectable can belong to many CollectableList

The price cannot be negative.

---

# CollectableTag

Represents the relationship between a Collectable and a Tag.

- collectable Collectable.id
- tag Tag.id

### Constraints

The combination of `collectable` and `tag` must be unique.

```text
Collectable N ─── N Tag
```

---

# Impression

Represents a user's evaluation or review of a Collectable.

- id uuid
- content text
- rating int
- images url[]
- writer User.id
- collectable Collectable.id

### Relationships

- Impression belongs to one User
- Impression belongs to one Collectable

### Rules

- `rating` must be between 1 and 5.
- A User can have at most one Impression for the same Collectable.

```text
User 1 ─── N Impression

Collectable 1 ─── N Impression
```

---

# CollectableList

Represents a personal list of Collectables owned by a User.

- id uuid
- owner User.id
- type CollectableListType
- collectables Collectable[]

### CollectableListType

```text
Cart
Wishlist
```

### Relationships

- CollectableList belongs to one User
- CollectableList contains many Collectable
- Collectable can belong to many CollectableList

A User can have one Cart and one Wishlist.

---

# CollectableListItem

Represents a Collectable inside a CollectableList.

- list CollectableList.id
- collectable Collectable.id
- quantity int

The `quantity` represents how many units of a Collectable are present in a Cart.

For a Wishlist, the quantity can be treated as `1`.

### Constraints

The combination of `list` and `collectable` must be unique.

```text
CollectableList N ─── N Collectable
```

---

# Relationships Overview

```text
User
│
├── 1 ─── 0..1 Shop
│              │
│              ├── 1 ─── N Collectable
│              │              │
│              │              ├── N ─── N Tag
│              │              │
│              │              └── 1 ─── N Impression
│              │
│              └── 1 ─── N Tag
│
├── 1 ─── N UserIdentity
│
├── 1 ─── N Session
│
├── 1 ─── N Impression
│
└── 1 ─── N CollectableList
                   │
                   └── N ─── N Collectable
```

# Authentication Overview

Authentication is separated from the User entity.

```text
User
│
├── UserIdentity
│   ├── Password
│   └── OpenIDConnect
│
└── Session
```

`UserIdentity` represents how the user authenticates.

`Session` represents a specific active login.

This allows the marketplace to support multiple authentication providers without coupling the User entity to a specific provider.

Possible OpenID Connect providers include:

```text
Google
GitHub
Microsoft
Keycloak
Auth0
```

The provider configuration itself is an infrastructure concern and does not belong to the User entity.

# Domain Rules

- A User can own at most one Shop.
- A Shop belongs to exactly one User.
- A Shop can contain multiple Collectables.
- A Shop can contain multiple Tags.
- A Tag belongs to exactly one Shop.
- A Tag can be associated with multiple Collectables.
- A Collectable belongs to exactly one Shop.
- A Collectable can have multiple Tags.
- A Collectable can have multiple Impressions.
- An Impression belongs to exactly one User and one Collectable.
- A User can have multiple authentication identities.
- A User can have multiple active Sessions.
- A User can have one Cart and one Wishlist.
- A CollectableList can contain multiple Collectables.
- A Cart item has a quantity.
- A Wishlist item represents a desired Collectable.
- Prices cannot be negative.
- Impression ratings must be between 1 and 5.
