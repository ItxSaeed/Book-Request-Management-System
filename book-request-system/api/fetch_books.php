<?php
/**
 * API Handler: Fetch books from Google Books API
 * Uses cURL with API Key + multiple fallback strategies
 * Rate limit: 5 calls per 24 hours per user
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

require_once '../config/db.php';
require_once '../includes/auth.php';

// Only allow logged-in users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['error' => 'Unauthorized. Please login first.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit();
}

$userId   = $_SESSION['user_id'];
$category = trim($_POST['category'] ?? '');

// ---- Category to multiple search queries (try different queries if one fails) ----
$categoryQueries = [
    'App Development'    => [
        'subject:web+development',
        'intitle:php+programming',
        'intitle:javascript+development',
        'intitle:web+application',
    ],
    'Mobile Development' => [
        'subject:mobile+development',
        'intitle:android+development',
        'intitle:flutter+dart',
        'intitle:ios+swift+programming',
    ],
    'AI' => [
        'subject:artificial+intelligence',
        'intitle:machine+learning',
        'intitle:deep+learning+neural',
        'intitle:data+science+python',
    ],
];

// ---- Hardcoded fallback books (always works, no internet needed) ----
$fallbackBooks = [
    'App Development' => [
        ['title' => 'Clean Code: A Handbook of Agile Software Craftsmanship', 'author' => 'Robert C. Martin'],
        ['title' => 'The Pragmatic Programmer: Your Journey to Mastery',      'author' => 'Andrew Hunt, David Thomas'],
        ['title' => 'JavaScript: The Good Parts',                             'author' => 'Douglas Crockford'],
        ['title' => 'PHP and MySQL Web Development',                          'author' => 'Luke Welling, Laura Thomson'],
        ['title' => 'Learning PHP MySQL and JavaScript',                      'author' => 'Robin Nixon'],
        ['title' => 'Web Development with Node and Express',                  'author' => 'Ethan Brown'],
        ['title' => 'HTML and CSS: Design and Build Websites',                'author' => 'Jon Duckett'],
        ['title' => 'You Dont Know JS: Scope and Closures',                   'author' => 'Kyle Simpson'],
        ['title' => 'Eloquent JavaScript: A Modern Introduction',             'author' => 'Marijn Haverbeke'],
        ['title' => 'Laravel: Up and Running',                                'author' => 'Matt Stauffer'],
        ['title' => 'Programming PHP: Creating Dynamic Web Pages',            'author' => 'Kevin Tatroe, Peter MacIntyre'],
        ['title' => 'CSS: The Definitive Guide',                              'author' => 'Eric A. Meyer, Estelle Weyl'],
        ['title' => 'Modern PHP: New Features and Good Practices',            'author' => 'Josh Lockhart'],
        ['title' => 'RESTful Web APIs: Services for a Changing World',        'author' => 'Leonard Richardson'],
        ['title' => 'Full Stack Web Development with Vue.js and Node',        'author' => 'Aneeta Sharma'],
        ['title' => 'Django for Beginners: Build Websites with Python',       'author' => 'William S. Vincent'],
        ['title' => 'Flask Web Development: Developing Web Applications',     'author' => 'Miguel Grinberg'],
        ['title' => 'ASP.NET Core in Action',                                 'author' => 'Andrew Lock'],
        ['title' => 'React: Up and Running',                                  'author' => 'Stoyan Stefanov'],
        ['title' => 'TypeScript Quickly',                                     'author' => 'Yakov Fain, Anton Moiseev'],
    ],
    'Mobile Development' => [
        ['title' => 'Flutter in Action',                                      'author' => 'Eric Windmill'],
        ['title' => 'Android Programming: The Big Nerd Ranch Guide',          'author' => 'Bill Phillips, Chris Stewart'],
        ['title' => 'iOS Programming: The Big Nerd Ranch Guide',              'author' => 'Christian Keur, Aaron Hillegass'],
        ['title' => 'React Native in Action',                                 'author' => 'Nader Dabit'],
        ['title' => 'Kotlin in Action',                                       'author' => 'Dmitry Jemerov, Svetlana Isakova'],
        ['title' => 'Swift Programming: The Big Nerd Ranch Guide',            'author' => 'Mikey Ward, Matthew Mathias'],
        ['title' => 'Head First Android Development',                         'author' => 'Dawn Griffiths, David Griffiths'],
        ['title' => 'iOS 16 App Development Essentials',                      'author' => 'Neil Smyth'],
        ['title' => 'Flutter and Dart Cookbook',                              'author' => 'Richard Rose'],
        ['title' => 'Building Mobile Apps at Scale',                          'author' => 'Gergely Orosz'],
        ['title' => 'Xamarin in Action: Creating native cross-platform apps', 'author' => 'Jim Bennett'],
        ['title' => 'Mobile Design Pattern Pocket Guide',                     'author' => 'Theresa Neil'],
        ['title' => 'Programming iOS 16: Dive Deep into Views',               'author' => 'Matt Neuburg'],
        ['title' => 'Android Internals: A Confectioners Cookbook',            'author' => 'Jonathan Levin'],
        ['title' => 'Ionic in Action: Hybrid Mobile Apps',                    'author' => 'Jeremy Wilken'],
        ['title' => 'Unity Mobile Game Development',                          'author' => 'John P. Doran'],
        ['title' => 'Mastering Flutter',                                      'author' => 'Pankaj Gupta'],
        ['title' => 'Android Studio 4.1 Development Essentials',              'author' => 'Neil Smyth'],
        ['title' => 'SwiftUI for Masterminds',                                'author' => 'J.D. Gauchat'],
        ['title' => 'Jetpack Compose by Tutorials',                           'author' => 'Rodrigo Bonifacio'],
    ],
    'AI' => [
        ['title' => 'Artificial Intelligence: A Modern Approach',             'author' => 'Stuart Russell, Peter Norvig'],
        ['title' => 'Deep Learning',                                          'author' => 'Ian Goodfellow, Yoshua Bengio, Aaron Courville'],
        ['title' => 'Hands-On Machine Learning with Scikit-Learn and TF',    'author' => 'Aurelien Geron'],
        ['title' => 'Pattern Recognition and Machine Learning',               'author' => 'Christopher M. Bishop'],
        ['title' => 'The Hundred-Page Machine Learning Book',                 'author' => 'Andriy Burkov'],
        ['title' => 'Machine Learning Yearning',                              'author' => 'Andrew Ng'],
        ['title' => 'Python Machine Learning',                                'author' => 'Sebastian Raschka, Vahid Mirjalili'],
        ['title' => 'Natural Language Processing with Python',                'author' => 'Steven Bird, Ewan Klein'],
        ['title' => 'Reinforcement Learning: An Introduction',                'author' => 'Richard S. Sutton, Andrew G. Barto'],
        ['title' => 'Mathematics for Machine Learning',                       'author' => 'Marc Peter Deisenroth'],
        ['title' => 'AI Superpowers: China, Silicon Valley and the New Order','author' => 'Kai-Fu Lee'],
        ['title' => 'Life 3.0: Being Human in the Age of Artificial Intel',  'author' => 'Max Tegmark'],
        ['title' => 'Superintelligence: Paths, Dangers, Strategies',          'author' => 'Nick Bostrom'],
        ['title' => 'Deep Learning with Python',                              'author' => 'Francois Chollet'],
        ['title' => 'Data Science from Scratch',                              'author' => 'Joel Grus'],
        ['title' => 'Machine Learning: A Probabilistic Perspective',          'author' => 'Kevin P. Murphy'],
        ['title' => 'Neural Networks and Deep Learning',                      'author' => 'Michael Nielsen'],
        ['title' => 'Applied Machine Learning',                               'author' => 'David Forsyth'],
        ['title' => 'The Elements of Statistical Learning',                   'author' => 'Trevor Hastie, Robert Tibshirani'],
        ['title' => 'Grokking Deep Learning',                                 'author' => 'Andrew W. Trask'],
    ],
];

if (!array_key_exists($category, $categoryQueries)) {
    echo json_encode(['error' => 'Invalid category selected.']);
    exit();
}

// ---- Rate Limit Check (5 calls per 24 hours) ----
try {
    $stmt = $pdo->prepare("SELECT * FROM api_rate_limit WHERE user_id = ?");
    $stmt->execute([$userId]);
    $rateRecord = $stmt->fetch();

    if ($rateRecord) {
        $hoursSinceReset = (time() - strtotime($rateRecord['last_reset'])) / 3600;
        if ($hoursSinceReset >= 24) {
            $pdo->prepare("UPDATE api_rate_limit SET call_count = 1, last_reset = NOW() WHERE user_id = ?")
                ->execute([$userId]);
        } elseif ($rateRecord['call_count'] >= 5) {
            $hoursLeft = round(24 - $hoursSinceReset, 1);
            echo json_encode(['error' => "Rate limit reached (5/day). Try again in {$hoursLeft} hours."]);
            exit();
        } else {
            $pdo->prepare("UPDATE api_rate_limit SET call_count = call_count + 1 WHERE user_id = ?")
                ->execute([$userId]);
        }
    } else {
        $pdo->prepare("INSERT INTO api_rate_limit (user_id, call_count, last_reset) VALUES (?, 1, NOW())")
            ->execute([$userId]);
    }
} catch (PDOException $e) {
    error_log("Rate limit error: " . $e->getMessage());
}

// ---- Check DB cache first ----
try {
    $stmt = $pdo->prepare("SELECT title, author FROM books WHERE category = ? ORDER BY title ASC");
    $stmt->execute([$category]);
    $cached = $stmt->fetchAll();
    if (count($cached) >= 10) {
        echo json_encode(['books' => $cached, 'source' => 'cache']);
        exit();
    }
} catch (PDOException $e) {
    error_log("Cache check error: " . $e->getMessage());
}

// ---- Try Google Books API with multiple query attempts ----
$apiBooks = [];
$queries  = $categoryQueries[$category];

foreach ($queries as $query) {
    if (count($apiBooks) >= 10) break; // Got enough books

    // Try without API key first, then could add key if needed
    $urls = [
        "https://www.googleapis.com/books/v1/volumes?q={$query}&maxResults=20&printType=books&langRestrict=en&orderBy=relevance",
        "https://www.googleapis.com/books/v1/volumes?q={$query}&maxResults=20&printType=books",
    ];

    foreach ($urls as $apiUrl) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Accept-Language: en-US,en;q=0.9',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode === 200) {
            $data = json_decode($response, true);
            if (isset($data['items']) && !empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $volInfo = $item['volumeInfo'] ?? [];
                    $title   = trim($volInfo['title'] ?? '');
                    $authors = $volInfo['authors'] ?? ['Unknown Author'];
                    $author  = trim(implode(', ', $authors));
                    if (!empty($title) && strlen($title) <= 250) {
                        // Avoid duplicates
                        $exists = false;
                        foreach ($apiBooks as $b) {
                            if (strtolower($b['title']) === strtolower($title)) {
                                $exists = true; break;
                            }
                        }
                        if (!$exists) {
                            $apiBooks[] = ['title' => $title, 'author' => $author];
                        }
                    }
                }
                break; // This URL worked, skip next URL
            }
        }

        // Small delay between retries
        usleep(200000); // 0.2 seconds
    }
}

// ---- Use API books if got enough, otherwise merge with fallback ----
if (count($apiBooks) >= 5) {
    $booksToInsert = $apiBooks;
} else {
    // Merge API results with fallback (fallback fills the gap)
    $booksToInsert = $fallbackBooks[$category];
    // Prepend any real API books at top
    foreach (array_reverse($apiBooks) as $ab) {
        array_unshift($booksToInsert, $ab);
    }
}

// ---- Insert into DB ----
try {
    $insertStmt = $pdo->prepare(
        "INSERT IGNORE INTO books (title, author, category) VALUES (?, ?, ?)"
    );
    foreach ($booksToInsert as $book) {
        $insertStmt->execute([$book['title'], $book['author'], $category]);
    }
} catch (PDOException $e) {
    error_log("Book insert error: " . $e->getMessage());
}

// ---- Return final list from DB ----
try {
    $stmt = $pdo->prepare("SELECT title, author FROM books WHERE category = ? ORDER BY title ASC");
    $stmt->execute([$category]);
    $allBooks = $stmt->fetchAll();
} catch (PDOException $e) {
    $allBooks = $booksToInsert;
}

if (empty($allBooks)) {
    $allBooks = $fallbackBooks[$category];
}

echo json_encode(['books' => $allBooks, 'count' => count($allBooks)]);
exit();
