<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Supported languages and metadata
$SUPPORTED_LANGUAGES = [
	'ar' => [ 'name' => 'العربية', 'dir' => 'rtl', 'flag' => '🇸🇦' ],
	'en' => [ 'name' => 'English', 'dir' => 'ltr', 'flag' => '🇬🇧' ],
	'fr' => [ 'name' => 'Français', 'dir' => 'ltr', 'flag' => '🇫🇷' ],
	'es' => [ 'name' => 'Español', 'dir' => 'ltr', 'flag' => '🇪🇸' ],
];

// Default language
$DEFAULT_LANG = 'ar';

// Handle language switch via GET or POST
$requestedLang = null;
if (isset($_GET['lang'])) {
	$requestedLang = strtolower(trim($_GET['lang']));
} elseif (isset($_POST['lang'])) {
	$requestedLang = strtolower(trim($_POST['lang']));
}

if ($requestedLang && array_key_exists($requestedLang, $SUPPORTED_LANGUAGES)) {
	$_SESSION['lang'] = $requestedLang;
}

// Determine current language
$CURRENT_LANG = isset($_SESSION['lang']) && array_key_exists($_SESSION['lang'], $SUPPORTED_LANGUAGES)
	? $_SESSION['lang']
	: $DEFAULT_LANG;

// Load dictionary
$i18n = [];
$fallback = [];
$langFilePath = __DIR__ . '/../lang/' . $CURRENT_LANG . '.json';
if (file_exists($langFilePath)) {
	$json = file_get_contents($langFilePath);
	$decoded = json_decode($json, true);
	if (is_array($decoded)) {
		$i18n = $decoded;
	}
}

// Load English fallback
$fallbackPath = __DIR__ . '/../lang/en.json';
if (file_exists($fallbackPath)) {
	$json = file_get_contents($fallbackPath);
	$decoded = json_decode($json, true);
	if (is_array($decoded)) {
		$fallback = $decoded;
	}
}

// Safe getter with dot notation support
function __t_get(array $array, string $path, $default = '') {
	$segments = explode('.', $path);
	$value = $array;
	foreach ($segments as $segment) {
		if (!is_array($value) || !array_key_exists($segment, $value)) {
			return $default;
		}
		$value = $value[$segment];
	}
	return is_string($value) ? $value : $default;
}

// Public translate helper
function __(string $key, array $replacements = []) {
	global $i18n, $fallback;
	$default = __t_get($fallback, $key, $key);
	$text = __t_get($i18n, $key, $default);
	foreach ($replacements as $search => $replace) {
		$text = str_replace('{' . $search . '}', $replace, $text);
	}
	return $text;
}

// Alias t()
function t(string $key, array $replacements = []) {
	return __($key, $replacements);
}

// Expose current language metadata
function currentLang() {
	global $CURRENT_LANG; return $CURRENT_LANG;
}

function currentDir() {
	global $SUPPORTED_LANGUAGES, $CURRENT_LANG; return $SUPPORTED_LANGUAGES[$CURRENT_LANG]['dir'];
}

function supportedLanguages() {
	global $SUPPORTED_LANGUAGES; return $SUPPORTED_LANGUAGES;
}
?>


