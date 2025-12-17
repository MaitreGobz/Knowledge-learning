# Data Dictionary — Knowledge Learning

## users

- id (PK, int)
- email (varchar, unique, not null)
- password_hash (varchar, not null)
- roles (varchar, not null)
- is_active (boolean, not null, default true)
- is_verified (boolean, not null, default false)
- created_at (datetime, not null)
- updated_at (datetime, not null)
- created_by (int)
- update_by (int)

## themes

- id (PK, int)
- title (varchar, not null)
- description (text, not null)
- slug (varchar, unique, not null)
- created_at (datetime, not null)
- updated_at (datetime, not null)
- created_by (int)
- update_by (int)

## cursus

- id (PK, int)
- theme_id (FK -> themes.id, not null)
- title (varchar, not null)
- description (text, nullable)
- price (int, not null) // price in cents
- is_active (boolean, not null, default true)
- created_at (datetime, not null)
- updated_at (datetime, not null)
- created_by (int)
- update_by (int)

## lessons

- id (PK, int)
- cursus_id (FK -> cursus.id, not null)
- title (varchar, not null)
- content (text, not null)
- video_url (varchar, nullable)
- position (int, not null)
- price (int, not null) // price in cents
- is_active (boolean, not null, default true)
- created_at (datetime, not null)
- updated_at (datetime, not null)
- created_by (int)
- update_by (int)

## purchases

- id (PK, int)
- user_id (FK -> users.id, not null)
- cursus_id (FK -> cursus.id, nullable)
- lesson_id (FK -> lessons.id, nullable)
- amount (int, not null)
- currency (varchar, not null, default EUR)
- status (varchar, not null) // PENDING | PAID | CANCELED
- stripe_session_id (varchar, unique, nullable)
- created_at (datetime, not null)
- updated_at (datetime, not null)
- created_by (int)
- update_by (int)

## access_rights

- id (PK, int)
- user_id (FK -> users.id, not null)
- cursus_id (FK -> cursus.id, nullable)
- lesson_id (FK -> lessons.id, nullable)
- granted_at (datetime, not null)
- purchase_id (FK -> purchases.id, nullable)
- created_at (datetime, not null)
- updated_at (datetime, not null)
- created_by (int)
- update_by (int)

## lesson_validations

- id (PK, int)
- user_id (FK -> users.id, not null)
- lesson_id (FK -> lessons.id, not null)
- validated_at (datetime, not null)
- created_at (datetime, not null)
- updated_at (datetime, not null)
- created_by (int)
- update_by (int)

## cursus_validations

- id (PK, int)
- user_id (FK -> users.id, not null)
- cursus_id (FK -> cursus.id, not null)
- validated_at (datetime, not null)
- created_at (datetime, not null)
- updated_at (datetime, not null)
- created_by (int)
- update_by (int)

## certifications

- id (PK, int)
- user_id (FK -> users.id, not null)
- theme_id (FK -> themes.id, not null)
- validated_at (datetime, not null)
- created_at (datetime, not null)
- updated_at (datetime, not null)
- created_by (int)
- update_by (int)
