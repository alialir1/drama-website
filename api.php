<?php
/**
 * وكيل API للموقع - يتصل بـ AnyShort API
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

const API_BASE = 'https://anyshort.net/v1';
const CACHE_DIR = __DIR__ . '/cache/';
const CACHE_TTL = 300;
const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';

if (!is_dir(CACHE_DIR)) {
    @mkdir(CACHE_DIR, 0755, true);
}

function makeRequest($endpoint, $params = [], $method = 'GET') {
    $url = API_BASE . $endpoint;
    if (!empty($params) && $method === 'GET') {
        $url .= '?' . http_build_query($params);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER => [
            'User-Agent: ' . USER_AGENT,
            'Accept-Language: ar-IQ,ar;q=0.9',
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        return json_decode($response, true);
    }

    return ['error' => true, 'message' => 'فشل الاتصال بـ API', 'code' => $httpCode];
}

function getFromCache($key) {
    $file = CACHE_DIR . md5($key) . '.json';
    if (is_file($file) && (time() - filemtime($file)) < CACHE_TTL) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}

function saveToCache($key, $data) {
    $file = CACHE_DIR . md5($key) . '.json';
    @file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));
}

$action = $_GET['action'] ?? null;
$lang = $_GET['lang'] ?? 'ar';

if (!$action) {
    echo json_encode(['error' => true, 'message' => 'حدد action'], JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($action) {
    case 'home':
        $cached = getFromCache('home_' . $lang);
        if ($cached) {
            echo json_encode($cached, JSON_UNESCAPED_UNICODE);
            exit;
        }
        $result = makeRequest('/home', ['lang' => $lang]);
        if (!isset($result['error'])) {
            saveToCache('home_' . $lang, $result);
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    case 'browse':
        $params = ['lang' => $lang];
        foreach (['cursor', 'limit', 'sort', 'offset'] as $k) {
            if (isset($_GET[$k])) $params[$k] = $_GET[$k];
        }
        $result = makeRequest('/browse', $params);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    case 'search':
        $q = $_GET['q'] ?? '';
        if (empty($q)) {
            echo json_encode(['error' => true, 'message' => 'أدخل نص البحث'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $params = ['q' => $q, 'lang' => $lang];
        $result = makeRequest('/search', $params);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    case 'title':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['error' => true, 'message' => 'حدد معرف المسلسل'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $result = makeRequest("/titles/$id", ['lang' => $lang]);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    case 'episodes':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(['error' => true, 'message' => 'حدد معرف المسلسل'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $params = ['lang' => $lang];
        foreach (['offset', 'limit'] as $k) {
            if (isset($_GET[$k])) $params[$k] = $_GET[$k];
        }
        $result = makeRequest("/titles/$id/episodes", $params);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    case 'play':
        $eid = $_GET['eid'] ?? '';
        if (empty($eid)) {
            echo json_encode(['error' => true, 'message' => 'حدد معرف الحلقة'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $params = ['lang' => $lang];
        $result = makeRequest("/episodes/$eid/play", $params);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    case 'subtitles':
        $eid = $_GET['eid'] ?? '';
        if (empty($eid)) {
            echo json_encode(['error' => true, 'message' => 'حدد معرف الحلقة'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $result = makeRequest("/episodes/$eid/subtitles", ['lang' => $lang]);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    case 'tags':
        $cached = getFromCache('tags_' . $lang);
        if ($cached) {
            echo json_encode($cached, JSON_UNESCAPED_UNICODE);
            exit;
        }
        $result = makeRequest('/tags', ['lang' => $lang]);
        if (!isset($result['error'])) {
            saveToCache('tags_' . $lang, $result);
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['error' => true, 'message' => 'إجراء غير معروف'], JSON_UNESCAPED_UNICODE);
}
?>