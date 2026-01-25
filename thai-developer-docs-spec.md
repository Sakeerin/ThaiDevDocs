# Thai Developer Docs (ThaiDevDocs)
## Technical Specification Document

**Version:** 1.0  
**Date:** January 2026  
**Project Type:** Developer Documentation Platform with CMS  

---

## 1. Executive Summary

### 1.1 Project Overview
Thai Developer Docs (ThaiDevDocs) เป็นแพลตฟอร์ม Documentation สำหรับนักพัฒนาที่เน้นเนื้อหาภาษาไทย ได้รับแรงบันดาลใจจาก MDN Web Docs โดยมีเป้าหมายเพื่อเป็นแหล่งความรู้ด้านการพัฒนาเว็บและซอฟต์แวร์ที่ครบถ้วนที่สุดในภาษาไทย

### 1.2 Vision
> "เป็นแหล่งความรู้ด้านการพัฒนาซอฟต์แวร์ภาษาไทยที่ครบถ้วน เข้าถึงง่าย และทันสมัยที่สุด"

### 1.3 Target Audience
- นักพัฒนาชาวไทยทุกระดับ (Beginner → Expert)
- นักศึกษาสาขาคอมพิวเตอร์และไอที
- ผู้ที่ต้องการเปลี่ยนสายอาชีพมาเป็นนักพัฒนา
- ครูอาจารย์และผู้สอนด้านโปรแกรมมิ่ง

---

## 2. MDN Web Docs Analysis

### 2.1 Site Structure Analysis

```
MDN Web Docs Architecture
├── 📚 Web Technologies (Reference)
│   ├── HTML
│   ├── CSS
│   ├── JavaScript
│   ├── Web APIs
│   ├── HTTP
│   ├── WebAssembly
│   └── Accessibility
│
├── 📖 Learn Web Development (Tutorial)
│   ├── Getting Started
│   ├── HTML Basics
│   ├── CSS Basics
│   ├── JavaScript Basics
│   ├── Front-end Development
│   ├── Server-side Development
│   └── Extensions & Tools
│
├── 🔧 Developer Tools
│   ├── Browser DevTools
│   └── Performance Tools
│
├── 👤 MDN Plus (Premium)
│   ├── Offline Access
│   ├── Collections
│   └── AI Help
│
└── 🌐 Community
    ├── Contribute
    ├── Blog
    └── GitHub Integration
```

### 2.2 Key Features Identified

| Feature Category | MDN Features | Priority for ThaiDevDocs |
|-----------------|--------------|-------------------------|
| **Search** | Full-text search, Filter by topic, Autocomplete | 🔴 Critical |
| **Content** | Markdown/MDX, Code examples, Interactive demos | 🔴 Critical |
| **Navigation** | Sidebar, Breadcrumbs, Related articles | 🔴 Critical |
| **User Features** | Bookmarks, Collections, Reading history | 🟡 High |
| **Localization** | Multi-language support | 🟢 Medium (Thai focus) |
| **Contribution** | GitHub integration, Edit suggestions | 🟡 High |
| **Premium** | Offline access, AI assistance | 🟢 Phase 2 |

### 2.3 Content Structure Analysis

MDN ใช้โครงสร้างเนื้อหาดังนี้:

```
Document Structure
├── Frontmatter (metadata)
│   ├── title
│   ├── slug
│   ├── tags
│   ├── browser_compat
│   └── spec_urls
│
├── Content Body
│   ├── Summary/Description
│   ├── Syntax
│   ├── Parameters/Properties
│   ├── Return Value
│   ├── Examples (Interactive)
│   ├── Specifications
│   ├── Browser Compatibility
│   └── See Also
│
└── Metadata
    ├── Last Updated
    ├── Contributors
    └── Source Link
```

---

## 3. System Architecture

### 3.1 High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         ThaiDevDocs Platform                         │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐          │
│  │   Frontend   │    │   Backend    │    │   Services   │          │
│  │   (Vue.js)   │◄──►│  (Laravel)   │◄──►│              │          │
│  └──────────────┘    └──────────────┘    └──────────────┘          │
│         │                   │                   │                   │
│         ▼                   ▼                   ▼                   │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐          │
│  │     CDN      │    │   Database   │    │  Search      │          │
│  │  (Assets)    │    │   (MySQL)    │    │  (Meilisearch)│         │
│  └──────────────┘    └──────────────┘    └──────────────┘          │
│                             │                   │                   │
│                             ▼                   ▼                   │
│                      ┌──────────────┐    ┌──────────────┐          │
│                      │    Redis     │    │   Storage    │          │
│                      │   (Cache)    │    │   (S3/Local) │          │
│                      └──────────────┘    └──────────────┘          │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### 3.2 Technology Stack

| Layer | Technology | Reason |
|-------|------------|--------|
| **Frontend** | Vue.js 3 + Nuxt 3 | SSR/SSG support, SEO friendly |
| **UI Framework** | Tailwind CSS + Headless UI | Customizable, Utility-first |
| **Backend** | Laravel 11 | Robust, Well-documented, Thai community |
| **Database** | MySQL 8 | Reliable, Full-text search support |
| **Cache** | Redis | Session, Cache, Queue |
| **Search** | Meilisearch | Fast, Typo-tolerant, Thai support |
| **Storage** | S3-Compatible | Scalable file storage |
| **Queue** | Laravel Horizon | Background jobs |
| **DevOps** | Docker + GitHub Actions | CI/CD Pipeline |

### 3.3 Detailed Architecture Diagram

```
                                    ┌─────────────────┐
                                    │   CloudFlare    │
                                    │   (CDN + WAF)   │
                                    └────────┬────────┘
                                             │
                    ┌────────────────────────┼────────────────────────┐
                    │                        │                        │
            ┌───────▼───────┐       ┌───────▼───────┐       ┌────────▼───────┐
            │  Web Server   │       │  API Server   │       │  Admin Panel   │
            │  (Nuxt SSR)   │       │   (Laravel)   │       │    (Vue SPA)   │
            │  Port: 3000   │       │  Port: 8000   │       │   Port: 8080   │
            └───────┬───────┘       └───────┬───────┘       └────────┬───────┘
                    │                       │                        │
                    └───────────────────────┼────────────────────────┘
                                            │
            ┌───────────────────────────────┼───────────────────────────────┐
            │                               │                               │
    ┌───────▼───────┐              ┌───────▼───────┐               ┌───────▼───────┐
    │     MySQL     │              │     Redis     │               │  Meilisearch  │
    │   (Primary)   │              │    (Cache)    │               │   (Search)    │
    │  Port: 3306   │              │  Port: 6379   │               │  Port: 7700   │
    └───────────────┘              └───────────────┘               └───────────────┘
```

---

## 4. Database Design

### 4.1 Entity Relationship Diagram

```
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│     users       │       │   categories    │       │     topics      │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id              │       │ id              │       │ id              │
│ name            │       │ name            │       │ category_id     │
│ email           │       │ slug            │       │ name            │
│ password        │       │ description     │       │ slug            │
│ avatar          │       │ icon            │       │ description     │
│ role            │       │ parent_id       │       │ icon            │
│ email_verified  │       │ order           │       │ order           │
│ created_at      │       │ is_active       │       │ is_active       │
│ updated_at      │       │ created_at      │       │ created_at      │
└────────┬────────┘       └────────┬────────┘       └────────┬────────┘
         │                         │                         │
         │                         │                         │
         │    ┌────────────────────┴─────────────────────────┘
         │    │
         │    │         ┌─────────────────┐
         │    │         │    articles     │
         │    │         ├─────────────────┤
         │    └────────►│ id              │
         │              │ topic_id        │◄────────────────────┐
         └─────────────►│ author_id       │                     │
                        │ title           │     ┌───────────────┴───────┐
                        │ slug            │     │  article_revisions    │
                        │ content         │     ├───────────────────────┤
                        │ content_html    │     │ id                    │
                        │ summary         │     │ article_id            │
                        │ difficulty      │     │ user_id               │
                        │ reading_time    │     │ content               │
                        │ view_count      │     │ content_html          │
                        │ status          │     │ change_summary        │
                        │ published_at    │     │ version               │
                        │ created_at      │     │ is_current            │
                        │ updated_at      │     │ created_at            │
                        └────────┬────────┘     └───────────────────────┘
                                 │
         ┌───────────────────────┼───────────────────────┐
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  article_tags   │    │ article_examples│    │article_bookmarks│
├─────────────────┤    ├─────────────────┤    ├─────────────────┤
│ id              │    │ id              │    │ id              │
│ article_id      │    │ article_id      │    │ article_id      │
│ tag_id          │    │ title           │    │ user_id         │
└─────────────────┘    │ language        │    │ created_at      │
         │             │ code            │    └─────────────────┘
         ▼             │ output          │
┌─────────────────┐    │ is_runnable     │
│      tags       │    │ order           │
├─────────────────┤    │ created_at      │
│ id              │    └─────────────────┘
│ name            │
│ slug            │
│ color           │
│ created_at      │
└─────────────────┘
```

### 4.2 Complete Database Schema

```sql
-- =====================================================
-- USERS & AUTHENTICATION
-- =====================================================

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) NULL,
    bio TEXT NULL,
    website VARCHAR(255) NULL,
    github_username VARCHAR(255) NULL,
    role ENUM('user', 'contributor', 'editor', 'admin', 'super_admin') DEFAULT 'user',
    contribution_points INT DEFAULT 0,
    remember_token VARCHAR(100) NULL,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    theme ENUM('light', 'dark', 'system') DEFAULT 'system',
    font_size ENUM('small', 'medium', 'large') DEFAULT 'medium',
    code_theme VARCHAR(50) DEFAULT 'github-dark',
    email_notifications BOOLEAN DEFAULT TRUE,
    weekly_digest BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_pref (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CONTENT STRUCTURE
-- =====================================================

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    icon VARCHAR(100) NULL,
    color VARCHAR(7) NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE topics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(100) NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    article_count INT DEFAULT 0,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_slug (category_id, slug),
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE articles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    topic_id BIGINT UNSIGNED NOT NULL,
    author_id BIGINT UNSIGNED NOT NULL,
    reviewer_id BIGINT UNSIGNED NULL,
    
    -- Basic Info
    title VARCHAR(500) NOT NULL,
    slug VARCHAR(500) NOT NULL,
    summary TEXT NULL,
    
    -- Content
    content LONGTEXT NOT NULL COMMENT 'Markdown content',
    content_html LONGTEXT NULL COMMENT 'Rendered HTML',
    table_of_contents JSON NULL COMMENT 'Auto-generated TOC',
    
    -- Classification
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    article_type ENUM('guide', 'reference', 'tutorial', 'example', 'glossary') DEFAULT 'guide',
    
    -- Statistics
    reading_time INT DEFAULT 0 COMMENT 'Minutes',
    view_count BIGINT DEFAULT 0,
    bookmark_count INT DEFAULT 0,
    
    -- Status
    status ENUM('draft', 'pending_review', 'published', 'archived') DEFAULT 'draft',
    is_featured BOOLEAN DEFAULT FALSE,
    is_pinned BOOLEAN DEFAULT FALSE,
    allow_comments BOOLEAN DEFAULT TRUE,
    
    -- Dates
    published_at TIMESTAMP NULL,
    last_reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- SEO
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    canonical_url VARCHAR(500) NULL,
    
    -- Version Control
    current_version INT DEFAULT 1,
    
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_topic_slug (topic_id, slug),
    INDEX idx_status (status),
    INDEX idx_published (published_at),
    INDEX idx_featured (is_featured),
    INDEX idx_difficulty (difficulty),
    FULLTEXT idx_search (title, summary, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_revisions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    
    content LONGTEXT NOT NULL,
    content_html LONGTEXT NULL,
    change_summary VARCHAR(500) NULL,
    
    version INT NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    is_major BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_article_version (article_id, version),
    INDEX idx_current (article_id, is_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CODE EXAMPLES & INTERACTIVE CONTENT
-- =====================================================

CREATE TABLE code_examples (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT UNSIGNED NOT NULL,
    
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    language VARCHAR(50) NOT NULL COMMENT 'html, css, javascript, php, etc.',
    
    code LONGTEXT NOT NULL,
    output TEXT NULL,
    output_type ENUM('text', 'html', 'console', 'image') DEFAULT 'text',
    
    is_runnable BOOLEAN DEFAULT FALSE,
    is_editable BOOLEAN DEFAULT FALSE,
    sandbox_config JSON NULL COMMENT 'Config for code sandbox',
    
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    INDEX idx_article (article_id),
    INDEX idx_language (language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE interactive_demos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT UNSIGNED NULL,
    
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    
    html_code LONGTEXT NULL,
    css_code LONGTEXT NULL,
    js_code LONGTEXT NULL,
    
    external_resources JSON NULL COMMENT 'CDN links, etc.',
    sandbox_type ENUM('iframe', 'codesandbox', 'stackblitz') DEFAULT 'iframe',
    
    is_public BOOLEAN DEFAULT TRUE,
    view_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TAGS & RELATIONSHIPS
-- =====================================================

CREATE TABLE tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NULL,
    color VARCHAR(7) DEFAULT '#6366F1',
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_tags (
    article_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (article_id, tag_id),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE related_articles (
    article_id BIGINT UNSIGNED NOT NULL,
    related_article_id BIGINT UNSIGNED NOT NULL,
    relation_type ENUM('prerequisite', 'see_also', 'next', 'previous') DEFAULT 'see_also',
    sort_order INT DEFAULT 0,
    
    PRIMARY KEY (article_id, related_article_id),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (related_article_id) REFERENCES articles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- USER INTERACTIONS
-- =====================================================

CREATE TABLE bookmarks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    article_id BIGINT UNSIGNED NOT NULL,
    collection_id BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_article (user_id, article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE collections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_slug (user_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reading_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    article_id BIGINT UNSIGNED NOT NULL,
    progress INT DEFAULT 0 COMMENT 'Reading progress percentage',
    time_spent INT DEFAULT 0 COMMENT 'Seconds',
    last_read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_article (user_id, article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_ratings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    is_helpful BOOLEAN NULL,
    feedback TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_article_user (article_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- COMMENTS & DISCUSSIONS
-- =====================================================

CREATE TABLE comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    
    content TEXT NOT NULL,
    content_html TEXT NULL,
    
    is_approved BOOLEAN DEFAULT TRUE,
    is_pinned BOOLEAN DEFAULT FALSE,
    upvote_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    
    INDEX idx_article (article_id),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comment_votes (
    user_id BIGINT UNSIGNED NOT NULL,
    comment_id BIGINT UNSIGNED NOT NULL,
    vote_type ENUM('up', 'down') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (user_id, comment_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- CONTRIBUTION SYSTEM
-- =====================================================

CREATE TABLE contributions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    article_id BIGINT UNSIGNED NOT NULL,
    
    type ENUM('create', 'edit', 'review', 'translate', 'fix_typo', 'add_example') NOT NULL,
    description TEXT NULL,
    points INT DEFAULT 0,
    
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    review_notes TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE edit_suggestions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    
    original_content TEXT NOT NULL,
    suggested_content TEXT NOT NULL,
    line_start INT NULL,
    line_end INT NULL,
    
    reason TEXT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    review_notes TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- GLOSSARY & REFERENCES
-- =====================================================

CREATE TABLE glossary (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    term VARCHAR(255) NOT NULL,
    term_en VARCHAR(255) NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    
    definition TEXT NOT NULL,
    definition_short VARCHAR(500) NULL,
    
    pronunciation VARCHAR(255) NULL,
    etymology TEXT NULL,
    
    related_terms JSON NULL,
    external_links JSON NULL,
    
    is_approved BOOLEAN DEFAULT TRUE,
    view_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FULLTEXT idx_search (term, term_en, definition)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BROWSER COMPATIBILITY
-- =====================================================

CREATE TABLE browsers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(255) NULL,
    color VARCHAR(7) NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE browser_compatibility (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT UNSIGNED NOT NULL,
    browser_id BIGINT UNSIGNED NOT NULL,
    
    support_status ENUM('yes', 'no', 'partial', 'unknown') DEFAULT 'unknown',
    version_added VARCHAR(50) NULL,
    version_removed VARCHAR(50) NULL,
    notes TEXT NULL,
    flags JSON NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (browser_id) REFERENCES browsers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_article_browser (article_id, browser_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SEARCH & ANALYTICS
-- =====================================================

CREATE TABLE search_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    query VARCHAR(500) NOT NULL,
    filters JSON NULL,
    results_count INT DEFAULT 0,
    clicked_article_id BIGINT UNSIGNED NULL,
    session_id VARCHAR(100) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_query (query(100)),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE page_views (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NULL,
    
    page_url VARCHAR(500) NOT NULL,
    referrer VARCHAR(500) NULL,
    
    session_id VARCHAR(100) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    country_code VARCHAR(2) NULL,
    
    time_on_page INT NULL COMMENT 'Seconds',
    scroll_depth INT NULL COMMENT 'Percentage',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_article (article_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SYSTEM & SETTINGS
-- =====================================================

CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group` VARCHAR(100) NOT NULL,
    `key` VARCHAR(100) NOT NULL,
    value TEXT NULL,
    type ENUM('string', 'integer', 'boolean', 'json', 'text') DEFAULT 'string',
    description TEXT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_group_key (`group`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    path VARCHAR(500) NOT NULL,
    disk VARCHAR(50) DEFAULT 'public',
    
    mime_type VARCHAR(100) NOT NULL,
    size BIGINT NOT NULL COMMENT 'Bytes',
    
    width INT NULL,
    height INT NULL,
    
    alt_text VARCHAR(255) NULL,
    caption TEXT NULL,
    
    metadata JSON NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_mime (mime_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    
    type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT NULL,
    data JSON NULL,
    
    action_url VARCHAR(500) NULL,
    
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, read_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- LEARNING PATHS
-- =====================================================

CREATE TABLE learning_paths (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id BIGINT UNSIGNED NOT NULL,
    
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NULL,
    
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    estimated_hours INT NULL,
    
    thumbnail VARCHAR(500) NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    is_published BOOLEAN DEFAULT FALSE,
    
    enrollment_count INT DEFAULT 0,
    completion_count INT DEFAULT 0,
    average_rating DECIMAL(3,2) NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE learning_path_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    learning_path_id BIGINT UNSIGNED NOT NULL,
    article_id BIGINT UNSIGNED NOT NULL,
    
    sort_order INT NOT NULL,
    is_required BOOLEAN DEFAULT TRUE,
    notes TEXT NULL,
    
    FOREIGN KEY (learning_path_id) REFERENCES learning_paths(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_path_article (learning_path_id, article_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_learning_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    learning_path_id BIGINT UNSIGNED NOT NULL,
    
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    
    current_item_id BIGINT UNSIGNED NULL,
    progress_percentage INT DEFAULT 0,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (learning_path_id) REFERENCES learning_paths(id) ON DELETE CASCADE,
    FOREIGN KEY (current_item_id) REFERENCES learning_path_items(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_user_path (user_id, learning_path_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 5. API Design

### 5.1 API Overview

```
Base URL: https://api.thaidevdocs.com/v1
Authentication: Bearer Token (Sanctum)
Rate Limiting: 60 requests/minute (guests), 120 requests/minute (authenticated)
```

### 5.2 API Endpoints

#### Authentication

```
POST   /auth/register          - Register new user
POST   /auth/login             - Login
POST   /auth/logout            - Logout
POST   /auth/refresh           - Refresh token
POST   /auth/forgot-password   - Request password reset
POST   /auth/reset-password    - Reset password
GET    /auth/user              - Get current user
PATCH  /auth/user              - Update profile
POST   /auth/social/{provider} - Social login (Google, GitHub)
```

#### Categories & Topics

```
GET    /categories                    - List all categories
GET    /categories/{slug}             - Get category details
GET    /categories/{slug}/topics      - Get topics in category
GET    /topics/{slug}                 - Get topic details
GET    /topics/{slug}/articles        - Get articles in topic
```

#### Articles

```
GET    /articles                      - List articles (paginated)
GET    /articles/featured             - Get featured articles
GET    /articles/popular              - Get popular articles
GET    /articles/recent               - Get recent articles
GET    /articles/{slug}               - Get article details
GET    /articles/{slug}/related       - Get related articles
GET    /articles/{slug}/revisions     - Get article revisions
GET    /articles/{slug}/examples      - Get code examples
GET    /articles/{slug}/compatibility - Get browser compatibility
```

#### Search

```
GET    /search                        - Search articles
GET    /search/suggestions            - Search suggestions/autocomplete
GET    /search/popular                - Popular search queries
```

#### User Features

```
GET    /bookmarks                     - Get user's bookmarks
POST   /bookmarks                     - Add bookmark
DELETE /bookmarks/{id}                - Remove bookmark

GET    /collections                   - Get user's collections
POST   /collections                   - Create collection
PATCH  /collections/{id}              - Update collection
DELETE /collections/{id}              - Delete collection

GET    /history                       - Get reading history
DELETE /history                       - Clear reading history

POST   /articles/{slug}/rate          - Rate article
POST   /articles/{slug}/feedback      - Submit feedback
```

#### Comments

```
GET    /articles/{slug}/comments      - Get article comments
POST   /articles/{slug}/comments      - Add comment
PATCH  /comments/{id}                 - Update comment
DELETE /comments/{id}                 - Delete comment
POST   /comments/{id}/vote            - Vote on comment
```

#### Contributions

```
GET    /contributions                 - Get user's contributions
POST   /articles/{slug}/suggest-edit  - Suggest edit
GET    /edit-suggestions              - Get pending suggestions (editors)
PATCH  /edit-suggestions/{id}         - Review suggestion
```

#### Learning Paths

```
GET    /learning-paths                - List learning paths
GET    /learning-paths/{slug}         - Get learning path details
POST   /learning-paths/{slug}/enroll  - Enroll in learning path
PATCH  /learning-paths/{slug}/progress - Update progress
GET    /my-learning                   - Get enrolled paths & progress
```

#### Glossary

```
GET    /glossary                      - List terms
GET    /glossary/{slug}               - Get term definition
GET    /glossary/search               - Search glossary
```

### 5.3 API Response Format

```json
// Success Response
{
    "success": true,
    "data": { ... },
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 100,
        "last_page": 5
    }
}

// Error Response
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid.",
        "details": {
            "email": ["The email field is required."]
        }
    }
}
```

### 5.4 Admin API Endpoints

```
PREFIX: /admin

// Dashboard
GET    /dashboard/stats              - Get dashboard statistics
GET    /dashboard/charts             - Get chart data

// Content Management
GET    /articles                     - List all articles (with filters)
POST   /articles                     - Create article
PUT    /articles/{id}                - Update article
DELETE /articles/{id}                - Delete article
PATCH  /articles/{id}/status         - Update article status
PATCH  /articles/{id}/publish        - Publish article

// Category Management
POST   /categories                   - Create category
PUT    /categories/{id}              - Update category
DELETE /categories/{id}              - Delete category
PATCH  /categories/reorder           - Reorder categories

// Topic Management
POST   /topics                       - Create topic
PUT    /topics/{id}                  - Update topic
DELETE /topics/{id}                  - Delete topic

// User Management
GET    /users                        - List users
GET    /users/{id}                   - Get user details
PATCH  /users/{id}/role              - Update user role
PATCH  /users/{id}/status            - Ban/unban user

// Comment Moderation
GET    /comments/pending             - Get pending comments
PATCH  /comments/{id}/approve        - Approve comment
PATCH  /comments/{id}/reject         - Reject comment

// Contribution Management
GET    /contributions/pending        - Get pending contributions
PATCH  /contributions/{id}/review    - Review contribution

// Settings
GET    /settings                     - Get all settings
PUT    /settings                     - Update settings

// Media
GET    /media                        - List media files
POST   /media                        - Upload media
DELETE /media/{id}                   - Delete media

// Analytics
GET    /analytics/overview           - Get analytics overview
GET    /analytics/articles           - Article performance
GET    /analytics/search             - Search analytics
GET    /analytics/users              - User analytics
```

---

## 6. Frontend Architecture

### 6.1 Project Structure

```
frontend/
├── app/
│   ├── components/
│   │   ├── common/
│   │   │   ├── AppHeader.vue
│   │   │   ├── AppFooter.vue
│   │   │   ├── AppSidebar.vue
│   │   │   ├── SearchModal.vue
│   │   │   ├── ThemeToggle.vue
│   │   │   └── BreadcrumbNav.vue
│   │   │
│   │   ├── article/
│   │   │   ├── ArticleContent.vue
│   │   │   ├── ArticleToc.vue
│   │   │   ├── ArticleMeta.vue
│   │   │   ├── ArticleNavigation.vue
│   │   │   ├── ArticleRating.vue
│   │   │   ├── CodeBlock.vue
│   │   │   ├── CodePlayground.vue
│   │   │   ├── BrowserCompat.vue
│   │   │   └── RelatedArticles.vue
│   │   │
│   │   ├── search/
│   │   │   ├── SearchInput.vue
│   │   │   ├── SearchResults.vue
│   │   │   ├── SearchFilters.vue
│   │   │   └── SearchSuggestions.vue
│   │   │
│   │   ├── user/
│   │   │   ├── UserAvatar.vue
│   │   │   ├── UserMenu.vue
│   │   │   ├── BookmarkButton.vue
│   │   │   └── ProgressTracker.vue
│   │   │
│   │   └── ui/
│   │       ├── BaseButton.vue
│   │       ├── BaseInput.vue
│   │       ├── BaseModal.vue
│   │       ├── BaseDropdown.vue
│   │       ├── BaseTabs.vue
│   │       ├── BaseToast.vue
│   │       └── LoadingSpinner.vue
│   │
│   ├── layouts/
│   │   ├── default.vue
│   │   ├── docs.vue
│   │   └── auth.vue
│   │
│   ├── pages/
│   │   ├── index.vue                 # Homepage
│   │   ├── search.vue                # Search results
│   │   ├── docs/
│   │   │   ├── index.vue             # Docs overview
│   │   │   └── [...slug].vue         # Dynamic article pages
│   │   ├── learn/
│   │   │   ├── index.vue             # Learning paths
│   │   │   └── [slug].vue            # Learning path detail
│   │   ├── glossary/
│   │   │   ├── index.vue             # Glossary list
│   │   │   └── [term].vue            # Term definition
│   │   ├── auth/
│   │   │   ├── login.vue
│   │   │   ├── register.vue
│   │   │   └── forgot-password.vue
│   │   └── user/
│   │       ├── profile.vue
│   │       ├── bookmarks.vue
│   │       ├── collections.vue
│   │       └── settings.vue
│   │
│   ├── composables/
│   │   ├── useAuth.ts
│   │   ├── useSearch.ts
│   │   ├── useBookmarks.ts
│   │   ├── useTheme.ts
│   │   ├── useToast.ts
│   │   └── useKeyboardShortcuts.ts
│   │
│   ├── stores/
│   │   ├── auth.ts
│   │   ├── search.ts
│   │   ├── bookmarks.ts
│   │   ├── preferences.ts
│   │   └── navigation.ts
│   │
│   ├── types/
│   │   ├── article.ts
│   │   ├── category.ts
│   │   ├── user.ts
│   │   └── api.ts
│   │
│   └── utils/
│       ├── api.ts
│       ├── markdown.ts
│       ├── highlight.ts
│       └── helpers.ts
│
├── assets/
│   ├── css/
│   │   ├── main.css
│   │   ├── prose.css
│   │   └── code-themes/
│   └── icons/
│
├── public/
│   ├── favicon.ico
│   └── og-image.png
│
├── nuxt.config.ts
├── tailwind.config.ts
├── tsconfig.json
└── package.json
```

### 6.2 Key Components Design

#### Homepage (index.vue)

```
┌────────────────────────────────────────────────────────────┐
│  🔍 Search Bar (Prominent)           [Login] [Theme]       │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  ┌──────────────────────────────────────────────────────┐ │
│  │                   Hero Section                        │ │
│  │  "เอกสารสำหรับนักพัฒนาภาษาไทย"                        │ │
│  │  [เริ่มเรียนรู้]  [ค้นหาเอกสาร]                       │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
│  Featured Categories                                       │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐     │
│  │   HTML   │ │   CSS    │ │JavaScript│ │   PHP    │     │
│  │   📄     │ │   🎨     │ │   ⚡     │ │   🐘     │     │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘     │
│                                                            │
│  📚 บทความยอดนิยม          📝 บทความล่าสุด                 │
│  ├─ บทความ 1               ├─ บทความ A                    │
│  ├─ บทความ 2               ├─ บทความ B                    │
│  └─ บทความ 3               └─ บทความ C                    │
│                                                            │
│  🎯 เส้นทางการเรียนรู้                                      │
│  ┌─────────────────┐ ┌─────────────────┐                  │
│  │ Frontend Path   │ │ Backend Path    │                  │
│  │ 12 บทเรียน      │ │ 15 บทเรียน      │                  │
│  └─────────────────┘ └─────────────────┘                  │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

#### Documentation Page (docs/[...slug].vue)

```
┌────────────────────────────────────────────────────────────┐
│  Logo    🔍 Search...                [Bookmark] [Settings] │
├──────────┬─────────────────────────────────┬───────────────┤
│          │                                 │               │
│ Sidebar  │      Article Content            │    TOC        │
│          │                                 │               │
│ 📂 HTML  │  📍 Home > HTML > Elements      │ On this page: │
│ ├─ Basic │                                 │ • Overview    │
│ ├─ Forms │  # <div> Element                │ • Syntax      │
│ └─ Media │                                 │ • Attributes  │
│          │  ดิวิชัน (Division) คือ...       │ • Examples    │
│ 📂 CSS   │                                 │ • ความเข้ากัน │
│ ├─ Box   │  ## Syntax                      │               │
│ └─ Grid  │  ```html                        │               │
│          │  <div>...</div>                 │               │
│ 📂 JS    │  ```                            │               │
│          │                                 │               │
│          │  ## Interactive Demo            │               │
│          │  ┌─────────────────────────┐   │               │
│          │  │   [Live Code Editor]    │   │               │
│          │  └─────────────────────────┘   │               │
│          │                                 │               │
│          │  ## Browser Compatibility       │               │
│          │  ┌─────────────────────────┐   │               │
│          │  │ Chrome ✓ | Firefox ✓   │   │               │
│          │  │ Safari ✓ | Edge ✓      │   │               │
│          │  └─────────────────────────┘   │               │
│          │                                 │               │
│          │  ─────────────────────────────  │               │
│          │  บทความนี้เป็นประโยชน์ไหม?       │               │
│          │  [👍 ใช่]  [👎 ไม่]              │               │
│          │                                 │               │
│          │  Related Articles              │               │
│          │  • <span> Element              │               │
│          │  • CSS Display Property        │               │
│          │                                 │               │
├──────────┴─────────────────────────────────┴───────────────┤
│  Footer: © 2026 ThaiDevDocs | About | Contact | GitHub     │
└────────────────────────────────────────────────────────────┘
```

### 6.3 UI Component Library

```typescript
// components/ui/BaseButton.vue
interface ButtonProps {
  variant: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger';
  size: 'sm' | 'md' | 'lg';
  loading?: boolean;
  disabled?: boolean;
  icon?: string;
  iconPosition?: 'left' | 'right';
}

// Design System Colors
const colors = {
  primary: {
    50: '#EEF2FF',
    500: '#6366F1',  // Indigo
    600: '#4F46E5',
    700: '#4338CA',
  },
  success: '#10B981',  // Emerald
  warning: '#F59E0B',  // Amber
  error: '#EF4444',    // Red
  
  gray: {
    50: '#F9FAFB',
    100: '#F3F4F6',
    200: '#E5E7EB',
    // ...
    900: '#111827',
  }
};

// Typography
const typography = {
  fontFamily: {
    sans: ['IBM Plex Sans Thai', 'Inter', 'sans-serif'],
    mono: ['JetBrains Mono', 'Fira Code', 'monospace'],
  },
  fontSize: {
    xs: '0.75rem',
    sm: '0.875rem',
    base: '1rem',
    lg: '1.125rem',
    xl: '1.25rem',
    '2xl': '1.5rem',
    '3xl': '1.875rem',
    '4xl': '2.25rem',
  }
};
```

---

## 7. Admin Panel (CMS)

### 7.1 Admin Panel Structure

```
admin-panel/
├── src/
│   ├── components/
│   │   ├── layout/
│   │   │   ├── AdminSidebar.vue
│   │   │   ├── AdminHeader.vue
│   │   │   └── AdminBreadcrumb.vue
│   │   │
│   │   ├── dashboard/
│   │   │   ├── StatsCards.vue
│   │   │   ├── RecentActivity.vue
│   │   │   ├── PopularArticles.vue
│   │   │   └── Charts/
│   │   │
│   │   ├── article/
│   │   │   ├── ArticleEditor.vue
│   │   │   ├── ArticleList.vue
│   │   │   ├── ArticlePreview.vue
│   │   │   ├── MarkdownEditor.vue
│   │   │   ├── CodeExampleEditor.vue
│   │   │   └── MetadataForm.vue
│   │   │
│   │   ├── media/
│   │   │   ├── MediaLibrary.vue
│   │   │   ├── MediaUploader.vue
│   │   │   └── ImageEditor.vue
│   │   │
│   │   └── common/
│   │       ├── DataTable.vue
│   │       ├── Pagination.vue
│   │       ├── SearchFilter.vue
│   │       └── StatusBadge.vue
│   │
│   ├── views/
│   │   ├── Dashboard.vue
│   │   ├── articles/
│   │   │   ├── Index.vue
│   │   │   ├── Create.vue
│   │   │   └── Edit.vue
│   │   ├── categories/
│   │   ├── topics/
│   │   ├── users/
│   │   ├── comments/
│   │   ├── contributions/
│   │   ├── glossary/
│   │   ├── learning-paths/
│   │   ├── media/
│   │   ├── analytics/
│   │   └── settings/
│   │
│   ├── router/
│   └── stores/
│
└── package.json
```

### 7.2 Admin Dashboard Wireframe

```
┌────────────────────────────────────────────────────────────────────┐
│  🏠 ThaiDevDocs Admin                        👤 Admin ▼   🔔       │
├──────────────┬─────────────────────────────────────────────────────┤
│              │                                                      │
│  📊 Dashboard│  Dashboard Overview                                  │
│              │                                                      │
│  📝 Articles │  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐│
│  ├─ All      │  │ Articles │ │  Users   │ │  Views   │ │ Comments ││
│  ├─ Create   │  │   156    │ │  2,450   │ │  45.2K   │ │   328    ││
│  ├─ Drafts   │  │  +12 ▲   │ │  +85 ▲   │ │ +5.2K ▲  │ │  +24 ▲   ││
│  └─ Pending  │  └──────────┘ └──────────┘ └──────────┘ └──────────┘│
│              │                                                      │
│  📂 Categories  ┌────────────────────────┐ ┌────────────────────┐  │
│              │  │   Views Over Time      │ │  Popular Articles  │  │
│  🏷️ Tags     │  │   📈 [Chart]          │ │  1. JavaScript...  │  │
│              │  │                        │ │  2. CSS Grid...    │  │
│  👥 Users    │  │                        │ │  3. Vue.js...      │  │
│              │  └────────────────────────┘ └────────────────────┘  │
│  💬 Comments │                                                      │
│              │  ┌────────────────────────────────────────────────┐ │
│  📚 Learning │  │  Recent Activity                               │ │
│     Paths    │  │  ──────────────────────────────────────────── │ │
│              │  │  👤 สมชาย created "CSS Flexbox Guide"         │ │
│  📖 Glossary │  │  👤 สมหญิง commented on "React Hooks"         │ │
│              │  │  👤 Admin approved contribution #45            │ │
│  📸 Media    │  └────────────────────────────────────────────────┘ │
│              │                                                      │
│  📊 Analytics│  ┌─────────────────────┐ ┌─────────────────────┐    │
│              │  │ Pending Reviews: 8  │ │ Edit Suggestions: 3│    │
│  ⚙️ Settings │  │ [Review Now →]      │ │ [View All →]        │    │
│              │  └─────────────────────┘ └─────────────────────┘    │
│              │                                                      │
└──────────────┴─────────────────────────────────────────────────────┘
```

### 7.3 Article Editor Interface

```
┌────────────────────────────────────────────────────────────────────┐
│  ← Back to Articles            Create New Article                  │
├────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  Title:                                                            │
│  ┌────────────────────────────────────────────────────────────┐   │
│  │ การใช้งาน CSS Grid Layout                                   │   │
│  └────────────────────────────────────────────────────────────┘   │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐  │
│  │ [Edit] [Preview] [Split]              [💾 Save Draft] [Publish]│
│  ├─────────────────────────────────────────────────────────────┤  │
│  │                                                              │  │
│  │  Toolbar: B I U | H1 H2 H3 | • 1. | 🔗 📷 📝 | </>          │  │
│  │  ─────────────────────────────────────────────────────────  │  │
│  │                                                              │  │
│  │  # CSS Grid Layout                                          │  │
│  │                                                              │  │
│  │  CSS Grid เป็นระบบ layout แบบ 2 มิติที่...                  │  │
│  │                                                              │  │
│  │  ## Basic Syntax                                            │  │
│  │                                                              │  │
│  │  ```css                                                     │  │
│  │  .container {                                               │  │
│  │    display: grid;                                           │  │
│  │    grid-template-columns: 1fr 1fr 1fr;                     │  │
│  │  }                                                          │  │
│  │  ```                                                        │  │
│  │                                                              │  │
│  │  :::tip                                                     │  │
│  │  ใช้ fr unit สำหรับการแบ่งพื้นที่อย่างยืดหยุ่น               │  │
│  │  :::                                                        │  │
│  │                                                              │  │
│  └─────────────────────────────────────────────────────────────┘  │
│                                                                     │
│  ┌──── Metadata ────────────────────────────────────────────────┐ │
│  │                                                               │ │
│  │  Category: [CSS ▼]     Topic: [Layout ▼]                     │ │
│  │                                                               │ │
│  │  Difficulty: ○ Beginner  ● Intermediate  ○ Advanced          │ │
│  │                                                               │ │
│  │  Tags: [CSS] [Grid] [Layout] [+Add]                          │ │
│  │                                                               │ │
│  │  Summary:                                                     │ │
│  │  ┌─────────────────────────────────────────────────────────┐ │ │
│  │  │ เรียนรู้การใช้งาน CSS Grid สำหรับสร้าง layout...        │ │ │
│  │  └─────────────────────────────────────────────────────────┘ │ │
│  │                                                               │ │
│  │  SEO Title: [                                               ] │ │
│  │  Meta Description: [                                        ] │ │
│  │                                                               │ │
│  └───────────────────────────────────────────────────────────────┘ │
│                                                                     │
│  ┌──── Code Examples ───────────────────────────────────────────┐ │
│  │  [+ Add Code Example]                                        │ │
│  │                                                               │ │
│  │  ┌─────────────────────────────────────────────────────────┐ │ │
│  │  │ Example 1: Basic Grid    [Edit] [Delete] [↑] [↓]        │ │ │
│  │  │ Language: CSS | Runnable: ✓                             │ │ │
│  │  └─────────────────────────────────────────────────────────┘ │ │
│  │                                                               │ │
│  └───────────────────────────────────────────────────────────────┘ │
│                                                                     │
└────────────────────────────────────────────────────────────────────┘
```

### 7.4 Admin Features Checklist

#### Content Management
- [ ] Article CRUD with Markdown editor
- [ ] Rich text editor with Thai language support
- [ ] Code syntax highlighting (multiple languages)
- [ ] Image upload and management
- [ ] Version history and comparison
- [ ] Draft/Preview/Publish workflow
- [ ] Schedule publishing
- [ ] Bulk operations (publish, delete, archive)

#### Category & Organization
- [ ] Category management (nested)
- [ ] Topic management
- [ ] Tag management with merge/cleanup
- [ ] Content reordering (drag & drop)

#### User Management
- [ ] User list with filters
- [ ] Role-based permissions
- [ ] User activity log
- [ ] Ban/suspend users
- [ ] Contribution tracking

#### Comment Moderation
- [ ] Pending comments queue
- [ ] Spam detection
- [ ] Bulk approve/reject
- [ ] Comment reporting

#### Contribution System
- [ ] Edit suggestion review
- [ ] Contribution points management
- [ ] Top contributors leaderboard

#### Learning Paths
- [ ] Create/edit learning paths
- [ ] Add articles to paths
- [ ] Reorder path items
- [ ] Track enrollment stats

#### Analytics Dashboard
- [ ] Overview metrics
- [ ] Article performance
- [ ] Search analytics
- [ ] User engagement
- [ ] Popular content
- [ ] Geographic distribution

#### Settings
- [ ] Site settings
- [ ] SEO configuration
- [ ] Email templates
- [ ] API keys management
- [ ] Backup/restore

---

## 8. Search Implementation

### 8.1 Meilisearch Configuration

```php
// config/scout.php
return [
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),
    
    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
    ],
];

// App\Models\Article.php
use Laravel\Scout\Searchable;

class Article extends Model
{
    use Searchable;
    
    public function searchableAs(): string
    {
        return 'articles';
    }
    
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => strip_tags($this->content_html),
            'category' => $this->topic->category->name,
            'topic' => $this->topic->name,
            'tags' => $this->tags->pluck('name')->toArray(),
            'difficulty' => $this->difficulty,
            'type' => $this->article_type,
            'published_at' => $this->published_at?->timestamp,
            'view_count' => $this->view_count,
        ];
    }
}
```

### 8.2 Search Index Settings

```php
// App\Console\Commands\ConfigureMeilisearch.php

$client->index('articles')->updateSettings([
    'searchableAttributes' => [
        'title',
        'summary', 
        'content',
        'tags',
        'category',
        'topic',
    ],
    'filterableAttributes' => [
        'category',
        'topic',
        'tags',
        'difficulty',
        'type',
        'published_at',
    ],
    'sortableAttributes' => [
        'published_at',
        'view_count',
    ],
    'rankingRules' => [
        'words',
        'typo',
        'proximity',
        'attribute',
        'sort',
        'exactness',
        'view_count:desc',
    ],
    // Thai language support
    'typoTolerance' => [
        'enabled' => true,
        'minWordSizeForTypos' => [
            'oneTypo' => 4,
            'twoTypos' => 8,
        ],
    ],
]);
```

### 8.3 Search API Implementation

```php
// App\Http\Controllers\Api\SearchController.php

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        $filters = [];
        
        if ($category = $request->input('category')) {
            $filters[] = "category = '{$category}'";
        }
        
        if ($difficulty = $request->input('difficulty')) {
            $filters[] = "difficulty = '{$difficulty}'";
        }
        
        if ($tags = $request->input('tags')) {
            $tagFilters = collect($tags)
                ->map(fn($tag) => "tags = '{$tag}'")
                ->join(' OR ');
            $filters[] = "({$tagFilters})";
        }
        
        $results = Article::search($query)
            ->options([
                'filter' => implode(' AND ', $filters),
                'sort' => [$request->input('sort', 'published_at:desc')],
                'limit' => $request->input('limit', 20),
                'offset' => $request->input('offset', 0),
                'attributesToHighlight' => ['title', 'summary', 'content'],
                'highlightPreTag' => '<mark>',
                'highlightPostTag' => '</mark>',
            ])
            ->raw();
        
        return response()->json([
            'success' => true,
            'data' => [
                'hits' => $results['hits'],
                'query' => $query,
                'processingTimeMs' => $results['processingTimeMs'],
                'estimatedTotalHits' => $results['estimatedTotalHits'],
            ],
        ]);
    }
    
    public function suggestions(Request $request)
    {
        $query = $request->input('q');
        
        $results = Article::search($query)
            ->options([
                'limit' => 5,
                'attributesToRetrieve' => ['id', 'title', 'slug', 'topic'],
            ])
            ->raw();
        
        return response()->json([
            'success' => true,
            'data' => $results['hits'],
        ]);
    }
}
```

---

## 9. Content Structure & Markdown

### 9.1 Markdown Extensions

```javascript
// markdown.config.js

import MarkdownIt from 'markdown-it';
import Prism from 'prismjs';

const md = new MarkdownIt({
  html: true,
  linkify: true,
  typographer: true,
  highlight: (str, lang) => {
    if (lang && Prism.languages[lang]) {
      return Prism.highlight(str, Prism.languages[lang], lang);
    }
    return '';
  },
});

// Custom containers
md.use(require('markdown-it-container'), 'tip', {
  render: (tokens, idx) => {
    if (tokens[idx].nesting === 1) {
      return '<div class="custom-block tip">\n<p class="custom-block-title">💡 Tip</p>\n';
    }
    return '</div>\n';
  },
});

md.use(require('markdown-it-container'), 'warning', {
  render: (tokens, idx) => {
    if (tokens[idx].nesting === 1) {
      return '<div class="custom-block warning">\n<p class="custom-block-title">⚠️ Warning</p>\n';
    }
    return '</div>\n';
  },
});

md.use(require('markdown-it-container'), 'info', {...});
md.use(require('markdown-it-container'), 'danger', {...});

// Interactive code block
md.use(require('markdown-it-container'), 'demo', {
  validate: (params) => params.trim().match(/^demo\s+(.*)$/),
  render: (tokens, idx) => {
    if (tokens[idx].nesting === 1) {
      const m = tokens[idx].info.trim().match(/^demo\s+(.*)$/);
      return `<CodePlayground id="${m[1]}">\n`;
    }
    return '</CodePlayground>\n';
  },
});
```

### 9.2 Article Template

```markdown
---
title: "CSS Grid Layout: คู่มือฉบับสมบูรณ์"
slug: css-grid-layout-guide
summary: "เรียนรู้การใช้งาน CSS Grid Layout ตั้งแต่พื้นฐานจนถึงขั้นสูง พร้อมตัวอย่างและ Interactive Demo"
difficulty: intermediate
type: guide
tags:
  - css
  - grid
  - layout
browser_compat: css-grid
prerequisites:
  - css-basics
  - css-box-model
---

# CSS Grid Layout

CSS Grid Layout เป็นระบบ layout แบบ 2 มิติที่ทรงพลังที่สุดใน CSS ช่วยให้คุณสามารถจัดวาง element ทั้งในแนวตั้งและแนวนอนได้อย่างง่ายดาย

## ภาพรวม

Grid Layout ประกอบด้วย...

:::tip
CSS Grid เหมาะสำหรับ layout แบบ 2 มิติ ในขณะที่ Flexbox เหมาะสำหรับ 1 มิติ
:::

## Syntax พื้นฐาน

```css
.container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-template-rows: auto;
  gap: 20px;
}
```

## ลองเล่นดู

:::demo grid-basic
```html
<div class="grid-container">
  <div class="item">1</div>
  <div class="item">2</div>
  <div class="item">3</div>
</div>
```

```css
.grid-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}
.item {
  background: #6366f1;
  padding: 20px;
  text-align: center;
  color: white;
}
```
:::

## Browser Compatibility

::browser-compat{feature="css-grid"}

## ดูเพิ่มเติม

- [[CSS Flexbox]]
- [[CSS Box Model]]
- [[Responsive Design]]
```

---

## 10. Development Plan

### 10.1 Project Phases

```
Phase 1: Foundation (4-6 สัปดาห์)
├── Week 1-2: Project Setup
│   ├── Laravel project initialization
│   ├── Database migrations
│   ├── Basic authentication
│   └── Admin panel scaffold
│
├── Week 3-4: Core Features
│   ├── Category/Topic CRUD
│   ├── Article CRUD with Markdown
│   ├── Code highlighting
│   └── Basic search
│
└── Week 5-6: Frontend Foundation
    ├── Nuxt.js setup
    ├── Design system implementation
    ├── Homepage
    └── Article pages

Phase 2: Features (4-6 สัปดาห์)
├── Week 7-8: User Features
│   ├── User registration/login
│   ├── Profile management
│   ├── Bookmarks
│   └── Reading history
│
├── Week 9-10: Advanced Content
│   ├── Code playground
│   ├── Browser compatibility
│   ├── Version history
│   └── Related articles
│
└── Week 11-12: Search & Navigation
    ├── Meilisearch integration
    ├── Advanced search filters
    ├── Navigation improvements
    └── SEO optimization

Phase 3: Community (3-4 สัปดาห์)
├── Week 13-14: Interactions
│   ├── Comments system
│   ├── Rating system
│   ├── Contribution system
│   └── Edit suggestions
│
└── Week 15-16: Learning
    ├── Learning paths
    ├── Progress tracking
    ├── Glossary
    └── Collections

Phase 4: Polish (2-3 สัปดาห์)
├── Performance optimization
├── Testing & bug fixes
├── Documentation
├── Content migration
└── Launch preparation
```

### 10.2 MVP Features (Phase 1)

**Must Have:**
- [x] Article viewing with Markdown rendering
- [x] Code syntax highlighting
- [x] Category & Topic navigation
- [x] Basic search
- [x] Responsive design
- [x] Admin: Article CRUD
- [x] Admin: Category management

**Should Have:**
- [ ] User authentication
- [ ] Bookmarks
- [ ] Dark mode
- [ ] Breadcrumb navigation

### 10.3 Tech Stack Summary

| Component | Technology | Version |
|-----------|------------|---------|
| Backend | Laravel | 11.x |
| Frontend | Nuxt.js | 3.x |
| UI | Tailwind CSS | 3.x |
| Database | MySQL | 8.x |
| Cache | Redis | 7.x |
| Search | Meilisearch | 1.x |
| Editor | Monaco Editor / CodeMirror | - |
| Markdown | markdown-it | - |
| Code Highlight | Prism.js | - |

---

## 11. Deployment Architecture

### 11.1 Production Setup

```yaml
# docker-compose.prod.yml
version: '3.8'

services:
  # API Server
  api:
    build:
      context: ./backend
      dockerfile: Dockerfile.prod
    environment:
      - APP_ENV=production
      - DB_HOST=mysql
      - REDIS_HOST=redis
      - MEILISEARCH_HOST=http://meilisearch:7700
    depends_on:
      - mysql
      - redis
      - meilisearch
    networks:
      - app-network

  # Frontend SSR
  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile.prod
    environment:
      - NUXT_API_URL=http://api:8000
    depends_on:
      - api
    networks:
      - app-network

  # Admin Panel
  admin:
    build:
      context: ./admin
      dockerfile: Dockerfile.prod
    environment:
      - VITE_API_URL=http://api:8000
    depends_on:
      - api
    networks:
      - app-network

  # Nginx Reverse Proxy
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/conf.d:/etc/nginx/conf.d
      - ./certbot/conf:/etc/letsencrypt
    depends_on:
      - api
      - frontend
      - admin
    networks:
      - app-network

  # MySQL
  mysql:
    image: mysql:8
    environment:
      - MYSQL_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
      - MYSQL_DATABASE=${DB_DATABASE}
      - MYSQL_USER=${DB_USERNAME}
      - MYSQL_PASSWORD=${DB_PASSWORD}
    volumes:
      - mysql-data:/var/lib/mysql
    networks:
      - app-network

  # Redis
  redis:
    image: redis:7-alpine
    volumes:
      - redis-data:/data
    networks:
      - app-network

  # Meilisearch
  meilisearch:
    image: getmeili/meilisearch:v1
    environment:
      - MEILI_MASTER_KEY=${MEILISEARCH_KEY}
    volumes:
      - meilisearch-data:/meili_data
    networks:
      - app-network

  # Queue Worker
  queue:
    build:
      context: ./backend
      dockerfile: Dockerfile.prod
    command: php artisan horizon
    depends_on:
      - api
      - redis
    networks:
      - app-network

  # Scheduler
  scheduler:
    build:
      context: ./backend
      dockerfile: Dockerfile.prod
    command: php artisan schedule:work
    depends_on:
      - api
    networks:
      - app-network

volumes:
  mysql-data:
  redis-data:
  meilisearch-data:

networks:
  app-network:
    driver: bridge
```

### 11.2 Nginx Configuration

```nginx
# nginx/conf.d/thaidevdocs.conf

# Frontend (Main site)
server {
    listen 80;
    listen 443 ssl http2;
    server_name thaidevdocs.com www.thaidevdocs.com;
    
    ssl_certificate /etc/letsencrypt/live/thaidevdocs.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/thaidevdocs.com/privkey.pem;
    
    location / {
        proxy_pass http://frontend:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}

# API
server {
    listen 80;
    listen 443 ssl http2;
    server_name api.thaidevdocs.com;
    
    ssl_certificate /etc/letsencrypt/live/api.thaidevdocs.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.thaidevdocs.com/privkey.pem;
    
    client_max_body_size 50M;
    
    location / {
        proxy_pass http://api:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

# Admin Panel
server {
    listen 80;
    listen 443 ssl http2;
    server_name admin.thaidevdocs.com;
    
    ssl_certificate /etc/letsencrypt/live/admin.thaidevdocs.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/admin.thaidevdocs.com/privkey.pem;
    
    location / {
        proxy_pass http://admin:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

---

## 12. Security Considerations

### 12.1 Security Checklist

- [ ] HTTPS everywhere (SSL/TLS)
- [ ] CSRF protection
- [ ] XSS prevention (sanitize markdown output)
- [ ] SQL injection prevention (Eloquent ORM)
- [ ] Rate limiting (API & Login)
- [ ] Input validation
- [ ] File upload validation
- [ ] Admin panel IP restriction (optional)
- [ ] Two-factor authentication for admins
- [ ] Regular security audits
- [ ] Dependency vulnerability scanning
- [ ] Backup encryption

### 12.2 Authentication Flow

```
User Registration:
1. Email + Password + Name
2. Email verification
3. Welcome email
4. First-time setup (optional preferences)

Social Login:
1. OAuth redirect (Google/GitHub)
2. Get user info
3. Create/link account
4. Generate session

Admin Login:
1. Email + Password
2. Two-factor authentication
3. Session with IP binding
4. Activity logging
```

---

## 13. Appendix

### 13.1 Initial Categories & Topics

```
📚 Web Technologies (เทคโนโลยีเว็บ)
├── HTML
│   ├── HTML พื้นฐาน
│   ├── HTML Elements
│   ├── HTML Forms
│   ├── HTML Tables
│   ├── HTML Media
│   └── HTML5 APIs
│
├── CSS
│   ├── CSS พื้นฐาน
│   ├── Selectors
│   ├── Box Model
│   ├── Flexbox
│   ├── Grid
│   ├── Responsive Design
│   ├── Animations
│   └── CSS Variables
│
├── JavaScript
│   ├── JavaScript พื้นฐาน
│   ├── DOM Manipulation
│   ├── Events
│   ├── Async Programming
│   ├── ES6+ Features
│   ├── Web APIs
│   └── Error Handling
│
└── Web APIs
    ├── Fetch API
    ├── Storage API
    ├── Canvas API
    └── Web Components

📖 Learn (เรียนรู้)
├── เริ่มต้นเขียนเว็บ
├── Frontend Development
├── Backend Development
├── Database
└── DevOps

🔧 Frameworks & Libraries
├── Vue.js
├── React
├── Laravel
├── Node.js
└── Tailwind CSS

📝 Guides (คู่มือ)
├── Best Practices
├── Design Patterns
├── Performance
├── Security
└── Testing
```

### 13.2 Glossary Sample Terms

| ภาษาไทย | English | คำอธิบาย |
|---------|---------|----------|
| ตัวแปร | Variable | ที่เก็บข้อมูลในโปรแกรม |
| ฟังก์ชัน | Function | กลุ่มคำสั่งที่ทำงานเฉพาะอย่าง |
| อาร์เรย์ | Array | โครงสร้างข้อมูลแบบรายการ |
| ลูป | Loop | การทำซ้ำ |
| เงื่อนไข | Condition | การตรวจสอบเพื่อตัดสินใจ |
| คลาส | Class | แม่แบบสำหรับสร้างออบเจกต์ |
| อินเทอร์เฟซ | Interface | ข้อกำหนดของเมธอด |
| การสืบทอด | Inheritance | การรับคุณสมบัติจากคลาสแม่ |

---

## 14. Summary

เอกสารนี้ครอบคลุมการออกแบบและวางแผนระบบ Thai Developer Docs ทั้งหมด ประกอบด้วย:

1. **System Architecture** - โครงสร้างระบบทั้ง Frontend และ Backend
2. **Database Design** - Schema ฐานข้อมูลครบถ้วน 25+ ตาราง
3. **API Design** - RESTful API สำหรับ Public และ Admin
4. **Frontend Architecture** - Nuxt.js project structure
5. **Admin Panel (CMS)** - ระบบจัดการเนื้อหาครบถ้วน
6. **Search Implementation** - Meilisearch configuration
7. **Content Structure** - Markdown extensions และ templates
8. **Development Plan** - 4 phases, ~16 สัปดาห์
9. **Deployment** - Docker-based production setup
10. **Security** - Checklist และ best practices

---

*Document Version: 1.0*  
*Last Updated: January 2026*
