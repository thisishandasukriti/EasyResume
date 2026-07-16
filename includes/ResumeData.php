<?php
/**
 * EasyResume - Universal Resume Data Structure
 * Single source of truth for all resume templates
 */

class ResumeData {
    public $personal = [];
    public $summary = '';
    public $skills = [];
    public $education = [];
    public $experience = [];
    public $projects = [];
    public $certifications = [];
    public $achievements = [];
    public $languages = [];
    public $interests = [];
    public $references = [];
    public $template = 'modern-sidebar';
    public $theme = [
        'primary' => '#6c63ff',
        'secondary' => '#3a1c8e',
        'accent' => '#9d8fff',
        'background' => '#ffffff',
        'text' => '#1c1a29',
        'heading' => '#1c1a29',
        'font_heading' => 'Poppins',
        'font_body' => 'Roboto'
    ];
    public $section_order = [
        'personal', 'summary', 'skills', 'experience', 'education', 
        'projects', 'certifications', 'achievements', 'languages', 'interests', 'references'
    ];
    public $section_visibility = [
        'personal' => true, 'summary' => true, 'skills' => true, 'experience' => true,
        'education' => true, 'projects' => true, 'certifications' => true,
        'achievements' => true, 'languages' => true, 'interests' => true, 'references' => false
    ];

    public function __construct($data = []) {
        $this->hydrate($data);
    }

    public function hydrate($data) {
        if (empty($data)) return;
        
        $this->personal = $data['personal'] ?? $this->getDefaultPersonal();
        $this->summary = $data['summary'] ?? '';
        $this->skills = $data['skills'] ?? [];
        $this->education = $data['education'] ?? [];
        $this->experience = $data['experience'] ?? [];
        $this->projects = $data['projects'] ?? [];
        $this->certifications = $data['certifications'] ?? [];
        $this->achievements = $data['achievements'] ?? [];
        $this->languages = $data['languages'] ?? [];
        $this->interests = $data['interests'] ?? [];
        $this->references = $data['references'] ?? [];
        $this->template = $data['template'] ?? 'modern-sidebar';
        $this->theme = array_merge($this->theme, $data['theme'] ?? []);
        $this->section_order = $data['section_order'] ?? $this->section_order;
        $this->section_visibility = array_merge($this->section_visibility, $data['section_visibility'] ?? []);
    }

    private function getDefaultPersonal() {
        return [
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'full_name' => '',
            'title' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'linkedin' => '',
            'github' => '',
            'portfolio' => '',
            'photo' => null,
            'photo_data' => null
        ];
    }

    public function getFullName() {
        $parts = array_filter([
            $this->personal['first_name'] ?? '',
            $this->personal['middle_name'] ?? '',
            $this->personal['last_name'] ?? ''
        ]);
        return implode(' ', $parts) ?: ($this->personal['full_name'] ?? '');
    }

    public function toArray() {
        return [
            'personal' => $this->personal,
            'summary' => $this->summary,
            'skills' => $this->skills,
            'education' => $this->education,
            'experience' => $this->experience,
            'projects' => $this->projects,
            'certifications' => $this->certifications,
            'achievements' => $this->achievements,
            'languages' => $this->languages,
            'interests' => $this->interests,
            'references' => $this->references,
            'template' => $this->template,
            'theme' => $this->theme,
            'section_order' => $this->section_order,
            'section_visibility' => $this->section_visibility
        ];
    }

    public function toJson() {
        return json_encode($this->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public static function fromJson($json) {
        $data = json_decode($json, true);
        return new self($data ?? []);
    }

    public static function fromSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['resume_data'])) {
            return new self($_SESSION['resume_data']);
        }
        return new self();
    }

    public function saveToSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['resume_data'] = $this->toArray();
    }

    public function saveToDatabase($pdo, $userId) {
        $stmt = $pdo->prepare("
            INSERT INTO resumes (user_id, data, template, theme, updated_at)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE data = VALUES(data), template = VALUES(template), theme = VALUES(theme), updated_at = NOW()
        ");
        return $stmt->execute([
            $userId,
            $this->toJson(),
            $this->template,
            json_encode($this->theme)
        ]);
    }

    public static function loadFromDatabase($pdo, $userId) {
        $stmt = $pdo->prepare("SELECT data, template, theme FROM resumes WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $data = json_decode($row['data'], true);
            $data['template'] = $row['template'];
            $data['theme'] = json_decode($row['theme'], true);
            return new self($data);
        }
        return new self();
    }

    public function getVisibleSections() {
        return array_filter($this->section_order, function($section) {
            return $this->section_visibility[$section] ?? true;
        });
    }

    public function hasSectionData($section) {
        switch ($section) {
            case 'personal': return !empty($this->getFullName());
            case 'summary': return !empty(trim($this->summary));
            case 'skills': return !empty($this->skills);
            case 'education': return !empty($this->education);
            case 'experience': return !empty($this->experience);
            case 'projects': return !empty($this->projects);
            case 'certifications': return !empty($this->certifications);
            case 'achievements': return !empty($this->achievements);
            case 'languages': return !empty($this->languages);
            case 'interests': return !empty($this->interests);
            case 'references': return !empty($this->references);
            default: return false;
        }
    }
}