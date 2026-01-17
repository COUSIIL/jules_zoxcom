<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

/* ========= helpers ========= */

function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

function safeStr($v, string $default = ''): string {
  return is_string($v) ? trim($v) : $default;
}

function safeInt($v, int $default): int {
  if (is_int($v)) return $v;
  if (is_numeric($v)) return (int)$v;
  return $default;
}

/**
 * Essaie d'extraire un JSON valide d'un texte
 */
function extractJson(string $text): ?array {
  $text = trim($text);
  if ($text === '') return null;

  $decoded = json_decode($text, true);
  if (is_array($decoded)) return $decoded;

  $start = strpos($text, '{');
  $end   = strrpos($text, '}');
  if ($start === false || $end === false || $end <= $start) return null;

  $slice = substr($text, $start, $end - $start + 1);
  $decoded2 = json_decode($slice, true);
  return is_array($decoded2) ? $decoded2 : null;
}

/**
 * POST JSON via cURL + retourne http/body/error/curlinfo
 */
function curlPostJson(string $url, array $headers, array $payload): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 120,
    CURLOPT_CONNECTTIMEOUT => 20,
  ]);
  $body = curl_exec($ch);
  $err  = curl_error($ch);
  $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $info = curl_getinfo($ch);
  curl_close($ch);

  return [$http, $body === false ? '' : $body, $err, $info];
}

/**
 * KIE peut renvoyer message.content soit:
 * - string
 * - array [{type:"text",text:"..."}]
 * On normalise vers string.
 */
function normalizeAssistantContentToText($content): string {
  if (is_string($content)) return $content;

  if (is_array($content)) {
    $parts = [];
    foreach ($content as $c) {
      if (is_array($c)) {
        $type = $c['type'] ?? '';
        if ($type === 'text' && isset($c['text']) && is_string($c['text'])) {
          $parts[] = $c['text'];
        }
      }
    }
    return implode("\n", $parts);
  }

  return '';
}

/* ========= bootstrap dotenv/autoload ========= */

$autoload = __DIR__ . '/../../../vendor/autoload.php';
if (!file_exists($autoload)) {
  respond(500, ["success" => false, "error" => "Autoload introuvable : $autoload"]);
}
require $autoload;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

/* ========= 1) Read FormData ========= */

$productName = safeStr($_POST['product_name'] ?? '');
$priceDa     = safeStr($_POST['price_da'] ?? '');
$whatsapp    = safeStr($_POST['whatsapp'] ?? '');
$delivery    = safeStr($_POST['delivery'] ?? '58 wilayas');

$aspect      = safeStr($_POST['aspect'] ?? '9:16', '9:16');
$language    = safeStr($_POST['language'] ?? 'dz_darija', 'dz_darija');

$maxSeconds     = safeInt($_POST['max_seconds'] ?? 40, 40);
$clipsCount     = safeInt($_POST['clips'] ?? 5, 5);
$secondsPerClip = safeInt($_POST['seconds_per_clip'] ?? 8, 8);

// Image URL
$imageUrl = safeStr($_POST['image_url'] ?? '');
if ($imageUrl === '') {
  respond(400, ["success" => false, "error" => "image_url manquante (champ 'image_url')"]);
}
if (!preg_match('#^https?://#i', $imageUrl)) {
  respond(400, ["success" => false, "error" => "image_url invalide (doit commencer par http/https)"]);
}

if ($productName === '' || $priceDa === '' || $whatsapp === '') {
  respond(400, [
    "success" => false,
    "error" => "Champs manquants. Requis: product_name, price_da, whatsapp"
  ]);
}

if ($clipsCount < 1) $clipsCount = 1;
if ($clipsCount > 5) $clipsCount = 5;
if ($maxSeconds > 40) $maxSeconds = 40;

/* ========= 2) Prompt ========= */

switch ($language) {
  case 'fr':
    $langInstruction = "Langue: Français.";
    break;
  case 'ar':
    $langInstruction = "Langue: Arabe (فصحى).";
    break;
  default:
    $langInstruction = "Langue: Darija Algérienne (avec sous-titres FR dans le script).";
}

$prompt = <<<PROMPT
Tu es un réalisateur publicitaire algérien (style DZ moderne), expert en vidéos e-commerce.

Objectif:
- À partir de la photo produit fournie, écrire un SCRIPT complet de publicité vidéo professionnelle.
- Durée totale MAX: {$maxSeconds} secondes.
- Format: {$aspect}
- Nombre de clips: {$clipsCount} (environ {$secondsPerClip}s chacun)
- {$langInstruction}

Contraintes:
- Pas de musique sous droits: proposer un style musical (ex: pop légère / raï soft) sans citer d'artiste connu.
- Pas d'allégations médicales dangereuses.
- Pas de texte incrusté dans la vidéo (on met les textes dans le script uniquement).
- Le résultat DOIT être un JSON valide.

Infos produit:
- Nom: {$productName}
- Prix: {$priceDa} DA
- WhatsApp: {$whatsapp}
- Livraison: {$delivery}

Sortie attendue (JSON):
- "script": voix off + timing (0-8s, 8-16s...) + sous-titres FR si darija.
- "storyboard": scène par scène: plan, mouvement caméra, lumière, ambiance, texte à l'écran (séparé), SFX.
- "video_prompts": tableau de {$clipsCount} prompts courts pour génération vidéo, cohérents.
Chaque prompt doit:
- dire "à partir de l'image fournie comme première frame"
- préciser mouvement caméra, lumière, style pub premium, réalisme
- NE PAS inclure de texte à l'écran

IMPORTANT: Réponds uniquement avec un JSON, sans texte autour.
PROMPT;

/* ========= 3) Call KIE Chat Completions ========= */

$API_KEY = $_ENV['KIE_API_KEY'] ?? '';
if (!$API_KEY) {
  respond(500, ['success' => false, 'error' => 'KIE_API_KEY introuvable (.env)']);
}

$url = 'https://api.kie.ai/gemini-3-pro/v1/chat/completions';

/**
 * NOTE:
 * - Je laisse response_format désactivé pour compatibilité max.
 * - Si tu confirmes que json_schema marche chez toi, on pourra le remettre.
 */
$payload = [
  "stream" => false,
  "include_thoughts" => false,
  "messages" => [
    [
      "role" => "developer",
      "content" => [
        ["type" => "text", "text" => "Tu réponds uniquement en JSON valide (sans texte autour)."]
      ]
    ],
    [
      "role" => "user",
      "content" => [
        ["type" => "text", "text" => $prompt],
        ["type" => "image_url", "image_url" => ["url" => $imageUrl]],
      ]
    ],
  ],
];

[$http, $body, $curlErr, $curlInfo] = curlPostJson(
  $url,
  [
    "Authorization: Bearer {$API_KEY}",
    "Content-Type: application/json",
  ],
  $payload
);

if ($curlErr !== '') {
  respond(502, [
    'success' => false,
    'error' => "Erreur cURL: {$curlErr}",
    'curl' => [
      'http' => $http,
      'total_time' => $curlInfo['total_time'] ?? null,
      'connect_time' => $curlInfo['connect_time'] ?? null,
      'namelookup_time' => $curlInfo['namelookup_time'] ?? null,
    ],
  ]);
}

if ($http < 200 || $http >= 300) {
  respond($http, [
    'success' => false,
    'error' => 'KIE API error (non-2xx)',
    'http' => $http,
    'raw' => $body
  ]);
}

$decoded = json_decode($body, true);
if (!is_array($decoded)) {
  respond(502, ['success' => false, 'error' => 'Réponse KIE non-JSON', 'raw' => $body]);
}

$content = $decoded['choices'][0]['message']['content'] ?? null;
$text = normalizeAssistantContentToText($content);

if (!is_string($text) || trim($text) === '') {
  respond(502, [
    'success' => false,
    'error' => 'Réponse vide du modèle (content inattendu)',
    'raw' => $decoded
  ]);
}

// Parse JSON output
$out = json_decode($text, true);
if (!is_array($out)) $out = extractJson($text);

if (!is_array($out)) {
  respond(502, [
    'success' => false,
    'error' => 'Impossible de parser le JSON renvoyé par KIE.',
    'raw_text' => $text
  ]);
}

/* ========= 4) Normalize output ========= */

if (!isset($out['video_prompts']) || !is_array($out['video_prompts'])) {
  $out['video_prompts'] = [];
}

$out['video_prompts'] = array_values(array_filter(
  $out['video_prompts'],
  fn($p) => is_string($p) && trim($p) !== ''
));

$out['video_prompts'] = array_slice($out['video_prompts'], 0, $clipsCount);

while (count($out['video_prompts']) < $clipsCount) {
  $out['video_prompts'][] =
    $out['video_prompts'][count($out['video_prompts']) - 1]
    ?? "Publicité produit premium, à partir de l'image fournie comme première frame, éclairage studio, caméra slow push-in, rendu réaliste, sans texte.";
}

respond(200, [
  'script' => (string)($out['script'] ?? ''),
  'storyboard' => $out['storyboard'] ?? '',
  'video_prompts' => $out['video_prompts'],
]);
