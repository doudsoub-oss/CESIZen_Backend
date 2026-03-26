# CESIZen Database Schema

## Entity Relationship Diagram

```mermaid
erDiagram
    %% ===== USER MANAGEMENT =====
    users {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at
        string password
        enum role "user, admin, super_admin"
        boolean is_active
        text two_factor_secret
        text two_factor_recovery_codes
        timestamp two_factor_confirmed_at
        timestamp created_at
        timestamp updated_at
    }

    %% ===== CONTENT MANAGEMENT =====
    categories {
        bigint id PK
        string name
        string slug UK
        text description
        bigint parent_id FK
        int position
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    contents {
        bigint id PK
        bigint category_id FK
        string title
        string slug UK
        text excerpt
        longtext body
        enum type "page, article, resource"
        boolean is_published
        timestamp published_at
        bigint created_by FK
        timestamp created_at
        timestamp updated_at
    }

    menus {
        bigint id PK
        string name
        enum location "main, footer, sidebar"
        timestamp created_at
        timestamp updated_at
    }

    menu_items {
        bigint id PK
        bigint menu_id FK
        bigint parent_id FK
        string title
        string url
        bigint content_id FK
        int position
        boolean is_active
        timestamp created_at
        timestamp updated_at
    }

    %% ===== DIAGNOSTIC MODULE =====
    questionnaires {
        bigint id PK
        string title
        string slug UK
        text description
        text instructions
        boolean is_active
        bigint created_by FK
        timestamp created_at
        timestamp updated_at
    }

    questions {
        bigint id PK
        bigint questionnaire_id FK
        text text
        text description
        int position
        boolean is_required
        timestamp created_at
        timestamp updated_at
    }

    answer_options {
        bigint id PK
        bigint question_id FK
        string label
        int score
        int position
        timestamp created_at
        timestamp updated_at
    }

    result_interpretations {
        bigint id PK
        bigint questionnaire_id FK
        int min_score
        int max_score
        string title
        text description
        text recommendations
        string color
        timestamp created_at
        timestamp updated_at
    }

    diagnostics {
        bigint id PK
        bigint user_id FK
        bigint questionnaire_id FK
        int score_total
        bigint result_interpretation_id FK
        timestamp completed_at
        timestamp created_at
        timestamp updated_at
    }

    diagnostic_responses {
        bigint id PK
        bigint diagnostic_id FK
        bigint question_id FK
        bigint answer_option_id FK
        int score
        timestamp created_at
    }

    %% ===== AUDIT & SECURITY =====
    audit_logs {
        bigint id PK
        bigint user_id FK
        string action
        string auditable_type
        bigint auditable_id
        json old_values
        json new_values
        string ip_address
        text user_agent
        timestamp created_at
    }

    %% ===== RELATIONSHIPS =====

    %% User relationships
    users ||--o{ diagnostics : "completes"
    users ||--o{ contents : "creates"
    users ||--o{ questionnaires : "creates"
    users ||--o{ audit_logs : "generates"

    %% Category relationships
    categories ||--o{ categories : "has children"
    categories ||--o{ contents : "contains"

    %% Content relationships
    contents ||--o{ menu_items : "linked from"

    %% Menu relationships
    menus ||--o{ menu_items : "contains"
    menu_items ||--o{ menu_items : "has children"

    %% Questionnaire relationships
    questionnaires ||--o{ questions : "contains"
    questionnaires ||--o{ result_interpretations : "defines"
    questionnaires ||--o{ diagnostics : "used in"

    %% Question relationships
    questions ||--o{ answer_options : "has"
    questions ||--o{ diagnostic_responses : "answered in"

    %% Diagnostic relationships
    diagnostics ||--o{ diagnostic_responses : "contains"
    diagnostics }o--|| result_interpretations : "interpreted as"

    %% Answer relationships
    answer_options ||--o{ diagnostic_responses : "selected in"
```

## Simplified View by Module

### User Management
```mermaid
erDiagram
    users {
        bigint id PK
        string name
        string email UK
        string password
        enum role "user, admin, super_admin"
        boolean is_active
    }

    audit_logs {
        bigint id PK
        bigint user_id FK
        string action
        string auditable_type
        json old_values
        json new_values
    }

    users ||--o{ audit_logs : "generates"
```

### Content Management
```mermaid
erDiagram
    categories {
        bigint id PK
        string name
        string slug UK
        bigint parent_id FK
    }

    contents {
        bigint id PK
        bigint category_id FK
        string title
        string slug UK
        longtext body
        boolean is_published
    }

    menus {
        bigint id PK
        string name
        enum location "main, footer, sidebar"
    }

    menu_items {
        bigint id PK
        bigint menu_id FK
        bigint parent_id FK
        string title
        bigint content_id FK
    }

    categories ||--o{ categories : "parent"
    categories ||--o{ contents : "contains"
    menus ||--o{ menu_items : "contains"
    menu_items ||--o{ menu_items : "parent"
    contents ||--o{ menu_items : "linked"
```

### Diagnostic Module (Core Feature)
```mermaid
erDiagram
    questionnaires {
        bigint id PK
        string title
        text description
        boolean is_active
    }

    questions {
        bigint id PK
        bigint questionnaire_id FK
        text text
        int position
    }

    answer_options {
        bigint id PK
        bigint question_id FK
        string label
        int score
    }

    result_interpretations {
        bigint id PK
        bigint questionnaire_id FK
        int min_score
        int max_score
        string title
        text recommendations
    }

    diagnostics {
        bigint id PK
        bigint user_id FK
        bigint questionnaire_id FK
        int score_total
    }

    diagnostic_responses {
        bigint id PK
        bigint diagnostic_id FK
        bigint question_id FK
        bigint answer_option_id FK
        int score
    }

    questionnaires ||--o{ questions : "contains"
    questionnaires ||--o{ result_interpretations : "defines"
    questionnaires ||--o{ diagnostics : "used in"
    questions ||--o{ answer_options : "has"
    questions ||--o{ diagnostic_responses : "answered"
    answer_options ||--o{ diagnostic_responses : "selected"
    diagnostics ||--o{ diagnostic_responses : "contains"
    diagnostics }o--|| result_interpretations : "result"
```

## Example Data Flow

### How a Diagnostic Works

```mermaid
sequenceDiagram
    participant U as User
    participant Q as Questionnaire
    participant Qs as Questions
    participant AO as Answer Options
    participant D as Diagnostic
    participant DR as Diagnostic Responses
    participant RI as Result Interpretation

    U->>Q: Start questionnaire
    Q->>Qs: Load questions (ordered by position)
    Qs->>AO: Load answer options for each question

    loop For each question
        U->>AO: Select an answer
        AO->>DR: Store response with score
    end

    DR->>D: Calculate total score
    D->>RI: Find matching score range
    RI->>U: Display result & recommendations
```

## Tables Summary

| Module | Table | Purpose |
|--------|-------|---------|
| **Users** | `users` | User accounts with roles |
| **Users** | `audit_logs` | Security logging |
| **Content** | `categories` | Content organization |
| **Content** | `contents` | Information pages |
| **Content** | `menus` | Navigation menus |
| **Content** | `menu_items` | Menu entries |
| **Diagnostic** | `questionnaires` | Stress questionnaires |
| **Diagnostic** | `questions` | Questions in questionnaire |
| **Diagnostic** | `answer_options` | Possible answers with scores |
| **Diagnostic** | `result_interpretations` | Score range meanings |
| **Diagnostic** | `diagnostics` | Completed questionnaires |
| **Diagnostic** | `diagnostic_responses` | User's answers |

## Key Improvements Over Original Schema

1. **Proper scoring system**: Questions have multiple `answer_options`, each with its own score
2. **Response tracking**: `diagnostic_responses` stores every answer the user gave
3. **Result configuration**: `result_interpretations` allows admins to define what score ranges mean
4. **Content hierarchy**: `categories` with `parent_id` for nested organization
5. **Navigation**: `menus` and `menu_items` for configurable navigation
6. **Audit trail**: `audit_logs` for RGPD compliance and security
7. **Soft delete**: `is_active` flags instead of hard deletes
