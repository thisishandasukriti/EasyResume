<?php
// ─────────────────────────────────────────────
//  AI Configuration
//  Edit keys and flags here — never elsewhere.
// ─────────────────────────────────────────────

define('GEMINI_API_KEY',   'YOUR_GEMINI_API_KEY_HERE');
define('OPENAI_API_KEY',   'YOUR_OPENAI_API_KEY_HERE');

// 'gemini' | 'openai'
define('AI_PROVIDER',      'gemini');

// Model strings
define('GEMINI_MODEL',     'gemini-1.5-flash');
define('OPENAI_MODEL',     'gpt-4o-mini');

// Set true to return mock responses (no real API calls, free development)
define('USE_MOCK_AI',      true);

// Max tokens for AI responses
define('AI_MAX_TOKENS',    1024);

// Session key used to pass resume data between pages
define('SESSION_RESUME_KEY', 'ai_resume_text');
