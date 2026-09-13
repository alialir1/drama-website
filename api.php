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
const USER_AGENT = 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36';

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
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_ENCODING => 'gzip, deflate',
        CURLOPT_HTTPHEADER => [
            'Host: anyshort.net',
            'User-Agent: ' . USER_AGENT,
            'Accept-Language: ar-IQ,ar;q=0.9',
            'Accept: application/json, text/plain, */*',
            'Accept-Encoding: gzip, deflate, br',
            'Origin: https://anyshort.net',
            'Referer: https://anyshort.net/ar/',
            'Connection: keep-alive',
            'Cache-Control: max-age=0',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // معالجة الأخطاء
    if ($curlError) {
        return ['error' => true, 'message' => 'خطأ في الاتصال: ' . $curlError, 'code' => 0];
    }

    if (!$response) {
        return ['error' => true, 'message' => 'لم يتم الحصول على رد من الخادم', 'code' => $httpCode];
    }

    if ($httpCode !== 200) {
        return ['error' => true, 'message' => 'خطأ من الخادم: ' . $httpCode, 'code' => $httpCode, 'response' => substr($response, 0, 200)];
    }

    $decoded = json_decode($response, true);
    if ($decoded === null) {
        return ['error' => true, 'message' => 'رد غير صالح من API', 'code' => $httpCode];
    }

    return $decoded;
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

// إضافة بيانات تجريبية للاختبار
function getDemoData($action) {
    switch ($action) {
        case 'home':
            return [
                'data' => [
                    'featured' => [
                        ['id' => 1, 'title' => 'مسلسل تجريبي 1', 'poster' => 'https://via.placeholder.com/200x300?text=Demo1', 'rating' => '8.5', 'description' => 'وصف تجريبي'],
                        ['id' => 2, 'title' => 'مسلسل تجريبي 2', 'poster' => 'https://via.placeholder.com/200x300?text=Demo2', 'rating' => '8.0', 'description' => 'وصف تجريبي'],
                    ],
                    'results' => [
                        ['id' => 3, 'title' => 'فيلم تجريبي 1', 'poster' => 'https://via.placeholder.com/200x300?text=Film1', 'rating' => '7.5', 'description' => 'وصف تجريبي'],
                    ]
                ]
            ];
        case 'browse':
            return [
                'data' => [
                    ['id' => 4, 'title' => 'محتوى تجريبي', 'poster' => 'https://via.placeholder.com/200x300?text=Browse', 'rating' => '8.2', 'description' => 'وصف'],
                ]
            ];
        default:
            return ['data' => []];
    }
}

$action = $_GET['action'] ?? null;
$lang = $_GET['lang'] ?? 'ar';
$demo = isset($_GET['demo']); // اضغط ?demo=1 للاختبار

if (!$action) {
    echo json_encode(['error' => true, 'message' => 'حدد action'], JSON_UNESCAPED_UNICODE);
    exit;
}

// إذا كان وضع التجريب - استخدم بيانات وهمية
if ($demo) {
    $result = getDemoData($action);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
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
