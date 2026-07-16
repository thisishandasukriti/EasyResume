# EasyResume — Universal Resume Builder Architecture

EasyResume is a PHP-based resume builder that follows a "write once, render everywhere" architecture. Users enter their information a single time through one universal form and can instantly switch between multiple resume templates without re-entering any data.

The project combines:

* 8 interchangeable resume templates
* A universal resume builder
* Shared resume data structures
* Authentication and session management
* AI-powered resume tools
* ATS score analysis
* Template management
* Print-friendly PDF exports
* Database-backed persistence (Sprint 1 roadmap)

---

## Core Philosophy

Instead of maintaining one form per template, EasyResume uses:

```text
             Universal Resume Builder
                        |
                        |
                     $resume
                        |
              -------------------------
              |           |            |
          Templates    AI Tools       ATS
              |           |            |
              -------------------------
                        |
                  Resume Preview
                        |
                   Print / PDF
```

Every template receives the exact same `$resume` array. Templates are only responsible for presentation and never maintain their own data structures.

This provides:

* One form for every template
* Easy template switching
* Shared AI integrations
* Easier maintenance
* Future extensibility

---

## Project Architecture

```text
User
 |
 |
template-selection.php
 |
 |
resume-builder.php
 |
 | POST
 |
save_resume.php
 |
 -----------------------------
 |                           |
Session                    MySQL
(Current)             (Sprint 1 Roadmap)
 |                           |
 -----------------------------
             |
             |
     template-preview.php
             |
             |
     resume_renderer.php
             |
             |
    templates_registry.php
             |
             |
         Templates
             |
     --------------------------
     |            |            |
   ATS         AI Tools     Print
  Score                     PDF
```

---

## File Structure

```text
/
├── resume_data.php
├── templates_registry.php
├── resume_renderer.php
├── resume-builder.php
├── save_resume.php
├── template-selection.php
├── template-preview.php
│
├── api/
│   ├── ai_handler.php
│   ├── ats_score.php
│   ├── coverletter.php
│   ├── extract.php
│   ├── improve.php
│   └── role_fit.php
│
├── assets/
│   ├── css/
│   ├── images/
│   ├── js/
│   ├── resume-base.css
│   ├── builder.css
│   └── builder.js
│
├── config/
│   ├── ai_config.php
│   ├── certifications.php
│   ├── high_priority_skills.php
│   ├── role_profiles.php
│   ├── skills.php
│   └── soft_skills.php
│
├── includes/
│   ├── auth.php
│   ├── csrf.php
│   ├── db.php
│   ├── ResumeData.php
│   └── TemplateEngine.php
│
├── templates/
│   ├── modern-sidebar/
│   ├── traditional-corporate/
│   ├── executive-professional/
│   ├── minimal-ats/
│   ├── elegant-two-column/
│   ├── modern-card/
│   ├── clean-academic/
│   └── contemporary-creative/
│
├── login.php
├── signup.php
├── logout.php
├── index.php
├── home.php
├── faq.php
├── manage_templates.php
├── templates.php
└── README.md
```

---

## Template System

EasyResume currently provides 8 interchangeable templates:

| Template               | Category     |
| ---------------------- | ------------ |
| Modern Sidebar         | Modern       |
| Traditional Corporate  | Professional |
| Executive Professional | Executive    |
| Minimal ATS            | ATS Friendly |
| Elegant Two Column     | Professional |
| Modern Card            | Modern       |
| Clean Academic         | Academic     |
| Contemporary Creative  | Creative     |

Adding a new template requires only:

```text
templates/
   |
   ---- your-template/
            |
         index.php
         style.css

+
templates_registry.php
```

No other files require modification.

---

## Universal Resume Data Structure

Every component works with a single shared data structure.

```php
$resume = [

'personal' => [
    'full_name',
    'professional_title',
    'email',
    'phone',
    'address',
    'linkedin',
    'github',
    'portfolio',
    'profile_picture'
],

'summary' => '',

'skills' => [],

'education' => [],

'experience' => [],

'projects' => [],

'certifications' => [],

'achievements' => [],

'languages' => [],

'interests' => [],

'references' => []

];
```

No template uses its own field names.

Examples:

```text
Modern Sidebar
        |
     $resume
        |
Executive Professional
        |
     $resume
        |
Minimal ATS
        |
     $resume
```

Only the presentation changes.

---

## Request Flow

### Step 1

```text
template-selection.php
```

Users select a template.

Every thumbnail is rendered using:

```text
template-preview.php
        |
     sample mode
        |
      iframe
```

No screenshots are used.

---

### Step 2

```text
resume-builder.php
```

The universal form contains:

* Personal Information
* Professional Summary
* Skills
* Education
* Work Experience
* Projects
* Certifications
* Achievements
* Languages
* Interests
* References

Previously saved resumes automatically populate the form.

---

### Step 3

```text
save_resume.php
```

Responsibilities:

* Validate POST data
* Normalize resume fields
* Store resume information
* Redirect to preview page

Current implementation uses:

```text
$_SESSION['resume']
```

Sprint 1 introduces:

```text
MySQL persistence

↓

easy resume

↓

resumes table

↓

MySQLi connection
```

---

### Step 4

```text
template-preview.php
```

Supported modes:

```text
mode=blank

↓

Displays placeholders


mode=sample

↓

Displays fictional data


mode=live

↓

Displays the user's resume
```

Toolbar mode:

```text
toolbar=1
```

Provides:

* Edit Resume
* Switch Templates
* Print Resume
* Export PDF
* Future AI tools integration

---

## Resume Renderer

Only one file may directly render templates.

```text
resume_renderer.php
```

Every template is rendered through:

```php
render_resume_template(
    $slug,
    $resume
);
```

This guarantees:

* Consistent rendering
* Centralized integrations
* Easier maintenance

---

## Placeholder System

Templates never invent content.

Instead of:

```php
echo $name;
```

they use:

```php
ph(
    $value,
    'full_name'
);
```

Empty values become:

```text
{{full_name}}

{{email}}

{{phone}}

{{institution}}
```

Placeholders:

* are render-time only
* are never stored
* are never written to the database

This allows:

```text
Blank Template

↓

Real Layout Preview

↓

No Fake Information

↓

Real User Data
```

---

## AI Features

EasyResume includes five AI-powered tools:

* Resume Improvement Suggestions
* ATS Score Analysis
* Cover Letter Generator
* Skill Extraction
* Role Fit Optimization

All tools consume the same shared data structure.

```text
              $resume
                  |
      --------------------------------
      |               |              |
     ATS            AI              PDF
    Score          Tools           Export
      |               |
      -----------------
               |
            Preview
```

No template-specific logic is required.

Future AI integrations can simply accept:

```php
function score_resume(
    array $resume
)

function improve_resume(
    array $resume
)

function generate_cover_letter(
    array $resume
)
```

---

## Styling System

Each template defines:

```css
--primary
--secondary
--accent
--background
--text
--heading

--font-heading
--font-body
```

Example:

```css
.resume.modern-sidebar{

--primary:#2454FF;

--secondary:#16234D;

--accent:#6C8CFF;

--background:#FFFFFF;

--text:#2B2B33;

}
```

Future theme support only needs to overwrite these variables.

No template duplication is required.

---

## Print & PDF Support

Every template:

```text
210mm × 297mm

↓

A4 Compatible

↓

Print Friendly

↓

Browser PDF Export
```

Features include:

* A4 page sizing
* Page break handling
* Print optimized layouts
* Flexbox-based compatibility
* PDF friendly rendering

Avoided intentionally:

* CSS animations
* Fixed positioning
* Background images with text
* Complex print layouts

---

## Database Architecture

Current database:

```text
easy resume
```

Tables:

```text
userinfo

cv_templates

login_attempts

resumes
(Sprint 1)
```

Connection details:

```text
localhost

↓

root

↓

(empty password)

↓

MySQLi
```

The entire project standardizes on:

```text
MySQLi
```

PDO is not used.

---

## Generated Files

```text
mnt/
   |
user-data/
   |
outputs/
   |
templates/
```

These directories are used for generated content and are not part of the official template system.

The official templates exist exclusively inside:

```text
templates/
```

---

## Future Improvements

Planned features include:

* Database persistence
* Drag and drop section ordering
* Resume versioning
* Theme customization
* AI-powered recommendations
* Advanced ATS analysis
* PDF export enhancements
* Template marketplace support
* Cloud resume storage
* User profile synchronization

Adding section ordering will simply introduce:

```php
'order' => [

'summary',
'skills',
'education',
'experience'

];
```

without modifying existing templates.

---

## Technology Stack

```text
Frontend
--------
HTML
CSS
JavaScript

Backend
-------
PHP

Database
--------
MySQL
MySQLi

Development Environment
----------------------
XAMPP

Architecture
-------------
Universal Resume Builder

AI Features
------------
Resume Improvement
ATS Analysis
Cover Letter Generation
Skill Extraction
Role Optimization

Printing
---------
A4 Print Support
Browser PDF Export
```

---

## Design Principles

EasyResume follows five core principles:

1. Write Once, Render Everywhere.
2. One Shared Resume Structure.
3. Templates Only Handle Presentation.
4. AI Features Work Across Every Template.
5. Future Features Should Require Minimal Changes.

By separating data, rendering, templates, and AI integrations, EasyResume provides a scalable architecture that allows new templates and features to be added without modifying the existing resume-building workflow.
