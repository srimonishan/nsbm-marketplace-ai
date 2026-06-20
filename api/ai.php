<?php
/**
 * Gemini AI API Endpoint - GreenLink Market
 * 
 * Handles:
 * - Shopping Assistant (chat with product recommendations)
 * - Gift Recommendation (analyze requests & suggest products)
 * - Dynamic Hero Generator (generate marketing content)
 */
require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    case 'chat':
        handleChat();
        break;
    case 'gift':
        handleGiftRecommendation();
        break;
    case 'hero':
        handleHeroGeneration();
        break;
    case 'test':
        handleTest();
        break;
    default:
        jsonResponse(false, 'Invalid action');
}

/**
 * AI Feature 1: Shopping Assistant
 * Chat with users and recommend available products from the database
 */
function handleChat() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Method not allowed');
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $message = trim($input['message'] ?? '');
    $context = $input['context'] ?? [];

    if (empty($message)) {
        jsonResponse(false, 'Message is required');
        return;
    }

    // Get available products from database for context
    $productModel = new Product();
    $categoryModel = new Category();
    
    $products = $productModel->getAll(50, 0);
    $categories = $categoryModel->getAll();
    
    // Build product catalog context
    $productCatalog = buildProductCatalog($products, $categories);
    
    // Build the prompt
    $systemPrompt = "You are an AI Shopping Assistant for GreenLink Market, the exclusive e-commerce platform for NSBM Green University students and staff in Sri Lanka.

Your role:
- Help students find products they need
- Recommend products from our available catalog
- Answer questions about products, pricing, and availability
- Be friendly, helpful, and knowledgeable about campus life
- Always recommend specific products from our catalog when relevant
- Prices are in Sri Lankan Rupees (Rs.)

AVAILABLE PRODUCT CATALOG:
{$productCatalog}

AVAILABLE CATEGORIES:
" . implode(', ', array_column($categories, 'name')) . "

GUIDELINES:
- Only recommend products that exist in our catalog above
- Copy the exact full catalog product name and include its price in every recommendation
- If asked about products we don't have, politely say so and suggest alternatives
- Keep responses concise but helpful
- Use a friendly, student-oriented tone
- You can help with product comparisons, budget recommendations, etc.";

    // Build conversation history
    $messages = [
        ['role' => 'user', 'parts' => [['text' => $systemPrompt . "\n\nUser message: " . $message]]]
    ];

    // If there's conversation context, include it
    if (!empty($context)) {
        $conversationContext = "Previous conversation:\n";
        foreach (array_slice($context, -6) as $msg) {
            $role = $msg['role'] === 'user' ? 'Student' : 'Assistant';
            $conversationContext .= "{$role}: {$msg['content']}\n";
        }
        $messages = [
            ['role' => 'user', 'parts' => [['text' => $systemPrompt . "\n\n" . $conversationContext . "\n\nNew message from student: " . $message]]]
        ];
    }

    $response = callGeminiAPI($messages);
    
    if ($response['success']) {
        jsonResponse(true, 'Response generated', [
            'reply' => $response['text'],
            'products' => extractProductSuggestions($response['text'], $products)
        ]);
    } else {
        // Fallback response if API fails
        $fallback = generateFallbackResponse($message, $products);
        jsonResponse(true, 'Response generated', [
            'reply' => $fallback,
            'products' => extractProductSuggestions($fallback, $products),
            'fallback' => true
        ]);
    }
}

/**
 * AI Feature 2: Gift Recommendation Assistant
 * Analyze user requests and suggest matching products from MySQL
 */
function handleGiftRecommendation() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(false, 'Method not allowed');
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $request = trim($input['request'] ?? '');
    $budget = $input['budget'] ?? null;
    $occasion = $input['occasion'] ?? '';
    $recipient = $input['recipient'] ?? '';

    if (empty($request)) {
        jsonResponse(false, 'Request is required');
        return;
    }

    // Get products from database
    $productModel = new Product();
    $products = $productModel->getAll(50, 0);
    $productCatalog = buildProductCatalog($products, []);

    $prompt = "You are a Gift Recommendation Expert for GreenLink Market (NSBM Green University, Sri Lanka).

A student needs help finding a gift. Analyze their request and suggest the BEST matching products from our catalog.

STUDENT'S REQUEST: {$request}
" . ($occasion ? "OCCASION: {$occasion}\n" : "") . 
($recipient ? "RECIPIENT: {$recipient}\n" : "") . 
($budget ? "BUDGET: Rs. {$budget}\n" : "") . "

AVAILABLE PRODUCTS:
{$productCatalog}

Please provide:
1. Top 3-5 gift recommendations from our catalog
2. Why each product is a good fit
3. A creative gift message suggestion
4. If budget is specified, stay within budget

Format your response in a friendly, helpful way. Include product names and prices.";

    $messages = [
        ['role' => 'user', 'parts' => [['text' => $prompt]]]
    ];

    $response = callGeminiAPI($messages);
    
    if ($response['success']) {
        jsonResponse(true, 'Recommendations generated', [
            'reply' => $response['text'],
            'products' => extractProductSuggestions($response['text'], $products)
        ]);
    } else {
        // Fallback: recommend based on price range
        $recommendations = [];
        foreach ($products as $product) {
            $price = $product['sale_price'] ?: $product['price'];
            if (!$budget || $price <= $budget) {
                $recommendations[] = $product;
            }
            if (count($recommendations) >= 5) break;
        }
        
        $fallbackText = "Here are some gift suggestions from our catalog:\n\n";
        foreach ($recommendations as $rec) {
            $price = $rec['sale_price'] ?: $rec['price'];
            $fallbackText .= "• **{$rec['name']}** - Rs. " . number_format($price, 2) . "\n";
        }
        $fallbackText .= "\nWould you like more specific recommendations?";
        
        jsonResponse(true, 'Recommendations generated', [
            'reply' => $fallbackText,
            'products' => extractProductSuggestions($fallbackText, $products),
            'fallback' => true
        ]);
    }
}

/**
 * AI Feature 3: Dynamic Hero Generator
 * Generate attractive marketing headlines, subtitles and CTA text
 */
function handleHeroGeneration() {
    $productModel = new Product();
    $featuredProducts = $productModel->getFeatured(5);
    
    $productNames = array_column($featuredProducts, 'name');
    $productList = implode(', ', array_slice($productNames, 0, 5));

    $prompt = "You are a creative marketing copywriter for GreenLink Market, an AI-powered e-commerce platform for NSBM Green University students in Sri Lanka.

Generate fresh, engaging marketing content for our homepage hero section.

Currently featured products: {$productList}

Generate EXACTLY this JSON format (no markdown, no code blocks, just pure JSON):
{
    \"headline\": \"A catchy headline (max 10 words, use HTML <span class='gradient-text'>highlighted words</span> for emphasis)\",
    \"subtitle\": \"A compelling subtitle (max 25 words, about the marketplace benefits for students)\",
    \"cta\": \"Call-to-action button text (2-4 words)\"
}

Requirements:
- Make it exciting and relevant to university students
- Mention AI, smart shopping, or campus life themes
- Keep it fresh and different each time
- The headline should be attention-grabbing
- Use the gradient-text span on 2-3 key words in the headline";

    $messages = [
        ['role' => 'user', 'parts' => [['text' => $prompt]]]
    ];

    $response = callGeminiAPI($messages);
    
    if ($response['success']) {
        // Try to parse JSON from response
        $text = $response['text'];
        // Remove markdown code blocks if present
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        $text = trim($text);
        
        $data = json_decode($text, true);
        
        if ($data && isset($data['headline'])) {
            jsonResponse(true, 'Hero content generated', $data);
        } else {
            // Return default content
            jsonResponse(true, 'Hero content generated', [
                'headline' => 'Shop <span class="gradient-text">Smarter</span> with AI-Powered <span class="gradient-text">Recommendations</span>',
                'subtitle' => 'Your campus marketplace reimagined with artificial intelligence. Find exactly what you need, faster.',
                'cta' => 'Explore Now'
            ]);
        }
    } else {
        // Fallback hero content
        $fallbacks = [
            [
                'headline' => 'Experience <span class="gradient-text">Smart Shopping</span> on Campus',
                'subtitle' => 'AI-powered recommendations tailored for NSBM students. Premium products, unbeatable prices.',
                'cta' => 'Shop Smart'
            ],
            [
                'headline' => 'Your <span class="gradient-text">AI Shopping</span> Companion Awaits',
                'subtitle' => 'Discover curated products for campus life with intelligent suggestions just for you.',
                'cta' => 'Get Started'
            ],
            [
                'headline' => '<span class="gradient-text">Premium</span> Products for <span class="gradient-text">Campus Life</span>',
                'subtitle' => 'GreenLink Market brings AI-driven recommendations and seamless shopping to campus life.',
                'cta' => 'Browse Now'
            ]
        ];
        
        $selected = $fallbacks[array_rand($fallbacks)];
        jsonResponse(true, 'Hero content generated', $selected);
    }
}

/**
 * Test Gemini API Connection
 */
function handleTest() {
    $messages = [
        ['role' => 'user', 'parts' => [['text' => 'Say "Hello! Gemini API is connected successfully to GreenLink Market." in exactly those words.']]]
    ];
    
    $response = callGeminiAPI($messages);
    
    if ($response['success']) {
        jsonResponse(true, 'API connected', ['reply' => $response['text']]);
    } else {
        jsonResponse(false, $response['error'] ?? 'Connection failed');
    }
}

/**
 * Call Gemini API
 */
function callGeminiAPI($messages) {
    $apiKeys = GEMINI_API_KEYS;
    
    if (empty($apiKeys)) {
        return ['success' => false, 'error' => 'API key not configured'];
    }

    $model = GEMINI_MODEL;
    $url = rtrim(GEMINI_API_URL, '/') . "/{$model}:generateContent";

    $payload = [
        'contents' => $messages,
        'generationConfig' => [
            'temperature' => 0.7,
            'topK' => 40,
            'topP' => 0.95,
            'maxOutputTokens' => 1024,
        ],
        'safetySettings' => [
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
        ]
    ];

    $lastError = 'Gemini request failed';
    foreach ($apiKeys as $index => $apiKey) {
        $requestBody = json_encode($payload);
        $response = false;
        $httpCode = 0;
        $error = '';

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'x-goog-api-key: ' . $apiKey
                ],
                CURLOPT_POSTFIELDS => $requestBody,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => true
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\nx-goog-api-key: {$apiKey}\r\n",
                    'content' => $requestBody,
                    'timeout' => 30,
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true
                ]
            ]);
            $response = @file_get_contents($url, false, $context);
            foreach ($http_response_header ?? [] as $header) {
                if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches)) {
                    $httpCode = (int) $matches[1];
                }
            }
            if ($response === false) {
                $lastPhpError = error_get_last();
                $error = $lastPhpError['message'] ?? 'HTTPS request failed';
            }
        }

        if ($error) {
            error_log('Gemini API transport error: ' . $error);
            return ['success' => false, 'error' => 'cURL error: ' . $error];
        }

        $data = json_decode($response, true);
        if ($httpCode === 200 && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return ['success' => true, 'text' => $data['candidates'][0]['content']['parts'][0]['text']];
        }

        $lastError = $data['error']['message'] ?? 'HTTP ' . $httpCode;
        error_log('Gemini API key ' . ($index + 1) . " returned HTTP {$httpCode}: {$lastError}");

        // A different credential can recover from invalid-key, permission, or
        // per-key quota errors. Other failures are request/model errors.
        if (!in_array($httpCode, [401, 403, 429], true)) {
            break;
        }
    }

    return ['success' => false, 'error' => $lastError];
}

/**
 * Build product catalog string for AI context
 */
function buildProductCatalog($products, $categories) {
    $catalog = "";
    foreach ($products as $product) {
        $price = $product['sale_price'] ?: $product['price'];
        $stock = $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock';
        $catalog .= "- {$product['name']} | Category: {$product['category_name']} | Price: Rs. " . number_format($price, 2) . " | {$stock}\n";
    }
    return $catalog;
}

/**
 * Convert products mentioned in an AI reply into safe, structured navigation
 * data for the chat widget. The browser never needs to infer product URLs.
 */
function extractProductSuggestions(string $reply, array $products, int $limit = 5): array {
    $suggestions = [];

    foreach ($products as $product) {
        if (stripos($reply, $product['name']) === false) {
            continue;
        }

        $price = (float) ($product['sale_price'] ?: $product['price']);
        $suggestions[] = [
            'id' => (int) $product['id'],
            'name' => $product['name'],
            'category' => $product['category_name'] ?? '',
            'price' => $price,
            'original_price' => (float) $product['price'],
            'on_sale' => !empty($product['sale_price']) && (float) $product['sale_price'] < (float) $product['price'],
            'image' => $product['image'] ?? null,
            'url' => rtrim(APP_URL, '/') . '/pages/product.php?id=' . (int) $product['id']
        ];

        if (count($suggestions) >= $limit) {
            break;
        }
    }

    return $suggestions;
}

/**
 * Generate fallback response when API is unavailable
 */
function generateFallbackResponse($message, $products) {
    $message = strtolower($message);
    
    $categoryKeywords = [
        'Electronics' => ['electronic', 'laptop', 'computer', 'phone', 'tablet', 'headphone', 'mouse'],
        'Books & Stationery' => ['book', 'stationery', 'calculator', 'notebook', 'study'],
        'Fashion & Apparel' => ['fashion', 'clothes', 'clothing', 'hoodie', 'backpack', 'shoes'],
        'Food & Beverages' => ['food', 'drink', 'beverage', 'coffee', 'snack'],
        'Sports & Fitness' => ['sport', 'fitness', 'gym', 'yoga', 'workout'],
        'Art & Crafts' => ['art', 'craft', 'paint', 'ceramic'],
        'Services' => ['service', 'tutor', 'design', 'lesson'],
        'Dorm & Living' => ['dorm', 'room', 'living', 'lamp', 'organizer']
    ];

    $matchedCategory = null;
    foreach ($categoryKeywords as $category => $keywords) {
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                $matchedCategory = $category;
                break 2;
            }
        }
    }

    $budget = null;
    if (preg_match('/(?:under|below|less than|max(?:imum)?|budget(?: of)?)\s*(?:rs\.?|lkr)?\s*([\d,]+)/i', $message, $matches)
        || preg_match('/(?:rs\.?|lkr)\s*([\d,]+)/i', $message, $matches)) {
        $budget = (float) str_replace(',', '', $matches[1]);
    }

    $matchedProducts = array_values(array_filter($products, function ($product) use ($matchedCategory, $budget) {
        $price = (float) ($product['sale_price'] ?: $product['price']);
        if ((int) $product['stock_quantity'] <= 0 || ($budget !== null && $price > $budget)) {
            return false;
        }
        return $matchedCategory === null || $product['category_name'] === $matchedCategory;
    }));

    if (str_contains($message, 'deal') || str_contains($message, 'discount') || str_contains($message, 'sale')) {
        usort($matchedProducts, function ($a, $b) {
            $aDiscount = $a['sale_price'] ? ((float) $a['price'] - (float) $a['sale_price']) : 0;
            $bDiscount = $b['sale_price'] ? ((float) $b['price'] - (float) $b['sale_price']) : 0;
            return $bDiscount <=> $aDiscount;
        });
    } elseif (str_contains($message, 'popular') || str_contains($message, 'best')) {
        usort($matchedProducts, function ($a, $b) {
            $ratingComparison = (float) $b['rating'] <=> (float) $a['rating'];
            return $ratingComparison !== 0
                ? $ratingComparison
                : (int) $b['total_reviews'] <=> (int) $a['total_reviews'];
        });
    }

    // Do not present the entire catalogue for an unrelated message.
    $hasProductIntent = $matchedCategory !== null
        || $budget !== null
        || str_contains($message, 'product')
        || str_contains($message, 'popular')
        || str_contains($message, 'best')
        || str_contains($message, 'deal');

    if ($hasProductIntent && !empty($matchedProducts)) {
        $response = "Here are the best matching products currently in stock:\n\n";
        foreach (array_slice($matchedProducts, 0, 5) as $product) {
            $price = $product['sale_price'] ?: $product['price'];
            $response .= "• **{$product['name']}** - Rs. " . number_format($price, 2) . "\n";
        }
        if ($budget !== null) {
            $response .= "\nAll of these are within your Rs. " . number_format($budget, 2) . " budget.";
        } else {
            $response .= "\nWould you like details or a comparison?";
        }
        return $response;
    }

    if ($hasProductIntent && empty($matchedProducts)) {
        if ($budget !== null) {
            return "I couldn't find an in-stock match within Rs. " . number_format($budget, 2) . ". Try a higher budget or another category.";
        }
        return "I couldn't find an in-stock product matching that request. Try another category or tell me your budget.";
    }
    
    // Generic response
    $responses = [
        "I'd be happy to help you find products! Could you tell me more about what you're looking for? We have categories like Electronics, Books, Fashion, Food, Sports, and more.",
        "Welcome to GreenLink Market! I can help you find the perfect product. What category are you interested in, or do you have a specific item in mind?",
        "Hi there! I'm your AI shopping assistant. Tell me what you need - whether it's for studies, sports, fashion, or daily campus life - and I'll find the best options for you!"
    ];
    
    return $responses[array_rand($responses)];
}
