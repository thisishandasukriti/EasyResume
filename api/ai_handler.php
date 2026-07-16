<?php
// ─────────────────────────────────────────────
//  AI Handler — Core dispatcher
//  Called internally by all api/*.php files.
//  To swap providers, change AI_PROVIDER in
//  config/ai_config.php — nothing else changes.
// ─────────────────────────────────────────────

require_once __DIR__ . '/../config/ai_config.php';

/**
 * Send a prompt to the configured AI provider.
 *
 * @param  string $prompt   The full prompt text.
 * @param  string $context  Optional system context / persona.
 * @return string           The AI-generated text response.
 */
function ai_complete(string $prompt, string $context = ''): string {
    if (USE_MOCK_AI) {
        return ai_mock_response($prompt);
    }

    try {

    return match (AI_PROVIDER) {

        'openai' =>
            ai_openai($prompt, $context),

        default =>
            ai_gemini($prompt, $context),

    };

    }
    catch (Throwable $e) {

        throw new RuntimeException(
            'AI service temporarily unavailable.'
        );

    }
}

// ── Gemini ────────────────────────────────────
function ai_gemini(string $prompt, string $context): string {
    $url  = 'https://generativelanguage.googleapis.com/v1beta/models/'
          . GEMINI_MODEL . ':generateContent?key=' . GEMINI_API_KEY;

    $body = ['contents' => [['parts' => [['text' => ($context ? $context . "\n\n" : '') . $prompt]]]]];

    $response = ai_http_post($url, $body);
    return trim(
        $response['candidates'][0]['content']['parts'][0]['text']
        ?? 'AI response unavailable.'
    );
}

// ── OpenAI ────────────────────────────────────
function ai_openai(string $prompt, string $context): string {
    $url  = 'https://api.openai.com/v1/chat/completions';
    $msgs = [];
    if ($context) $msgs[] = ['role' => 'system', 'content' => $context];
    $msgs[] = ['role' => 'user', 'content' => $prompt];

    $body = ['model' => OPENAI_MODEL, 'max_tokens' => AI_MAX_TOKENS, 'messages' => $msgs];

    $response = ai_http_post($url, $body, ['Authorization: Bearer ' . OPENAI_API_KEY]);
    return trim(
        $response['choices'][0]['message']['content']
        ?? 'AI response unavailable.'
    );
}

// ── HTTP helper ───────────────────────────────
function ai_http_post(string $url, array $body, array $extra_headers = []): array {
    $headers = array_merge(['Content-Type: application/json'], $extra_headers);
    $ch = curl_init($url);
    curl_setopt_array($ch, [

        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 30,

        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,

    ]);
    $raw = curl_exec($ch);

    if ($raw === false) {
        $error = curl_error($ch);
        curl_close($ch);

        throw new RuntimeException(
            'AI request failed: ' . $error
        );
    }

    $http_code = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    curl_close($ch);

    if ($http_code >= 400) {
        throw new RuntimeException(
            'AI provider returned HTTP '
            . $http_code
        );
    }

    return json_decode($raw, true) ?? [];
}

// ── Mock (development) ────────────────────────
function ai_mock_response(string $prompt): string {
    $lp = strtolower($prompt);

    if (str_contains($lp, 'improve') || str_contains($lp, 'rewrite')) {
        return "• Engineered scalable REST APIs serving 50k+ daily active users, reducing latency by 35%\n"
             . "• Collaborated cross-functionally with product and design teams to deliver 3 major features on schedule\n"
             . "• Automated data pipeline workflows using Python and Apache Airflow, saving 10+ hours of manual effort weekly";
    }
    if (str_contains($lp, 'cover letter')) {
        return "Dear Hiring Manager,\n\nI am writing to express my strong interest in the [Role] position at [Company]. "
             . "With a proven track record in building scalable software solutions and a passion for delivering measurable results, "
             . "I am confident I would be a valuable addition to your team.\n\n"
             . "Throughout my career, I have consistently delivered high-impact projects — from architecting microservices that "
             . "reduced infrastructure costs by 30% to leading cross-functional initiatives that improved team velocity by 25%.\n\n"
             . "I am particularly drawn to [Company]'s mission and the opportunity to contribute to [specific area]. "
             . "I would welcome the chance to discuss how my background aligns with your goals.\n\n"
             . "Sincerely,\n[Your Name]";
    }
    if (str_contains($lp, 'missing') || str_contains($lp, 'recommend')) {
        return "Based on the role requirements, consider developing expertise in:\n"
             . "1. Docker & Kubernetes — containerization is expected for senior roles\n"
             . "2. Cloud platforms (AWS/GCP) — add at least one certification\n"
             . "3. System design skills — practice designing distributed systems\n"
             . "Your current profile is strong in core development but light on DevOps exposure.";
    }
    return "AI analysis complete. Your resume shows strong technical foundations. "
         . "Consider quantifying achievements with specific metrics to improve ATS performance.";
}
