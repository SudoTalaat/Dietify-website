<?php
require_once __DIR__ . '/config.php';

// BMI Calculator Function
function calculateBMI($weight, $height) {
    // Convert height from cm to meters
    $heightInMeters = $height / 100;
    $bmi = $weight / ($heightInMeters * $heightInMeters);
    return round($bmi, 1);
}

// Get BMI Category and nutritional recommendations
function getBMIRecommendations($bmi, $age, $gender, $activityLevel) {
    $category = '';
    $caloriesAdjustment = 0;
    $proteinMultiplier = 1.0;
    $carbsMultiplier = 1.0;
    $fatsMultiplier = 1.0;
    
    if ($bmi < 18.5) {
        $category = 'Underweight';
        $caloriesAdjustment = 300; // Increase calories
        $proteinMultiplier = 1.2; // Higher protein for muscle building
        $carbsMultiplier = 1.1;
        $fatsMultiplier = 1.2;
    } elseif ($bmi >= 18.5 && $bmi < 25) {
        $category = 'Normal weight';
        $caloriesAdjustment = 0;
        $proteinMultiplier = 1.0;
        $carbsMultiplier = 1.0;
        $fatsMultiplier = 1.0;
    } elseif ($bmi >= 25 && $bmi < 30) {
        $category = 'Overweight';
        $caloriesAdjustment = -300; // Decrease calories
        $proteinMultiplier = 1.1; // Higher protein for satiety
        $carbsMultiplier = 0.9;
        $fatsMultiplier = 0.9;
    } else {
        $category = 'Obese';
        $caloriesAdjustment = -500; // Significant calorie reduction
        $proteinMultiplier = 1.2; // High protein for muscle preservation
        $carbsMultiplier = 0.8;
        $fatsMultiplier = 0.8;
    }
    
    return [
        'bmi' => $bmi,
        'category' => $category,
        'calories_adjustment' => $caloriesAdjustment,
        'protein_multiplier' => $proteinMultiplier,
        'carbs_multiplier' => $carbsMultiplier,
        'fats_multiplier' => $fatsMultiplier
    ];
}

// Calculate base metabolic rate using Mifflin-St Jeor Equation
function calculateBMR($weight, $height, $age, $gender) {
    if ($gender === 'male') {
        return (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
    } else {
        return (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
    }
}

// Calculate daily calories based on activity level
function calculateDailyCalories($bmr, $activityLevel) {
    $multipliers = [
        'sedentary' => 1.2,
        'lightly_active' => 1.375,
        'moderately_active' => 1.55,
        'very_active' => 1.725,
        'extra_active' => 1.9
    ];
    
    $multiplier = $multipliers[$activityLevel] ?? 1.2;
    return round($bmr * $multiplier);
}

// Calculate macronutrients based on calories and goals
function calculateMacronutrients($calories, $proteinMultiplier, $carbsMultiplier, $fatsMultiplier) {
    // Standard macro ratios: 40% carbs, 30% protein, 30% fats
    $proteinCalories = ($calories * 0.3) * $proteinMultiplier;
    $carbsCalories = ($calories * 0.4) * $carbsMultiplier;
    $fatsCalories = ($calories * 0.3) * $fatsMultiplier;
    
    return [
        'protein' => round($proteinCalories / 4), // 4 calories per gram
        'carbs' => round($carbsCalories / 4),     // 4 calories per gram
        'fats' => round($fatsCalories / 9)       // 9 calories per gram
    ];
}

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (isset($_GET['action']) && $_GET['action'] === 'api') {
    header('Content-Type: application/json');

    $userDataString = [];
    foreach ($user as $k => $v) {
        if (!in_array($k, ['password', 'id', 'created_at', 'updated_at']) && $v !== null && $v !== '') {
            $userDataString[] = "$k: " . $v;
        }
    }
    $userDataString = implode(", ", $userDataString);

    $cacheKey = 'recs_' . $userId . '_' . md5($userDataString);
    // REMOVED CACHE CHECK so it generates a fresh dynamic answer every single time
    // if (isset($_SESSION[$cacheKey]) && !isset($_GET['force'])) {
    //     echo json_encode(['success' => true, 'data' => $_SESSION[$cacheKey]]);
    //     exit();
    // }

    $res = $conn->query("SELECT id, name, type, description, image_path, price FROM products WHERE status='active'");
    $products = [];
    while ($row = $res->fetch_assoc()) {
        $products[] = "- " . $row['name'] . " (" . $row['type'] . "): " . $row['description'];
    }
    $productList = implode("\n", $products);

    // Calculate BMI and nutritional needs
    $bmi = calculateBMI($user['weight'], $user['height']);
    $bmr = calculateBMR($user['weight'], $user['height'], $user['age'], $user['gender']);
    $activityLevel = $user['activity_level'] ?? 'sedentary';
    $baseCalories = calculateDailyCalories($bmr, $activityLevel);
    
    $bmiRecs = getBMIRecommendations($bmi, $user['age'], $user['gender'], $activityLevel);
    $adjustedCalories = $baseCalories + $bmiRecs['calories_adjustment'];
    $macros = calculateMacronutrients($adjustedCalories, $bmiRecs['protein_multiplier'], $bmiRecs['carbs_multiplier'], $bmiRecs['fats_multiplier']);
    
    $prompt = "You are a personalized health and nutrition assistant. Here is the user's profile data: { {$userDataString} }.
    
User's BMI Analysis:
- BMI: {$bmi} ({$bmiRecs['category']})
- Base Calories: {$baseCalories}
- Adjusted Calories: {$adjustedCalories}
- Recommended Protein: {$macros['protein']}g
- Recommended Carbs: {$macros['carbs']}g
- Recommended Fats: {$macros['fats']}g

Task 1: Use the calculated nutritional values above ({$adjustedCalories} calories, {$macros['carbs']}g carbs, {$macros['protein']}g protein, {$macros['fats']}g fats) as these are scientifically calculated based on their BMI and profile data.
Task 2: Recommend EXACTLY 3 foods and EXACTLY 2 drinks from the following available products in our shop that best fit their BMI category ({$bmiRecs['category']}) and nutritional goals. Explain briefly why each is recommended.

IMPORTANT: You must respond ONLY in valid JSON format matching this EXACT structure. Use the calculated nutritional values provided above:
{
  \"bmi\": {$bmi},
  \"bmi_category\": \"{$bmiRecs['category']}\",
  \"daily_calories\": {$adjustedCalories},
  \"daily_carbs\": {$macros['carbs']},
  \"daily_protein\": {$macros['protein']},
  \"daily_fats\": {$macros['fats']},
  \"advice_summary\": \"A short 2 sentence greeting about their BMI category and nutritional goals.\",
  \"foods\": [
    {\"name\": \"Exact Product Name\", \"reason\": \"Short specific reason...\"}
  ],
  \"drinks\": [
    {\"name\": \"Exact Product Name\", \"reason\": \"Short specific reason...\"}
  ]
}

Our products:
{$productList}";

    $apiKey = $_ENV['GROQ_API_KEY'] ?? '';
    if (!$apiKey) {
        echo json_encode(['error' => 'API Key not configured.']);
        exit;
    }

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'llama-3.1-8b-instant',
            'messages' => [['role' => 'system', 'content' => $prompt]],
            'max_tokens' => 800,
            'temperature' => 0.7,
            'response_format' => ['type' => 'json_object']
        ]),
        CURLOPT_TIMEOUT => 60,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $data = json_decode($response, true);
    
    $reply = $data['choices'][0]['message']['content'] ?? null;
    
    if (!$reply) {
        echo json_encode(['error' => 'Could not generate recommendations at this time: ' . htmlspecialchars($response)]);
        exit();
    }
    
    $parsedData = json_decode($reply, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($parsedData['daily_calories'])) {
        echo json_encode(['error' => 'AI returned invalid JSON format.']);
        exit();
    }

    $enhancedFoods = [];
    foreach ($parsedData['foods'] as $f) {
        $nameEscaped = $conn->real_escape_string($f['name']);
        $pRes = $conn->query("SELECT id, name, image_path, price FROM products WHERE name LIKE '%{$nameEscaped}%' LIMIT 1");
        if ($pRes && $p = $pRes->fetch_assoc()) {
            $f['id'] = $p['id'];
            $f['image_path'] = getImageUrl($p['image_path']);
            $f['price'] = $p['price'];
        }
        $enhancedFoods[] = $f;
    }
    $parsedData['foods'] = $enhancedFoods;

    $enhancedDrinks = [];
    foreach ($parsedData['drinks'] as $d) {
        $nameEscaped = $conn->real_escape_string($d['name']);
        $pRes = $conn->query("SELECT id, name, image_path, price FROM products WHERE name LIKE '%{$nameEscaped}%' LIMIT 1");
        if ($pRes && $p = $pRes->fetch_assoc()) {
            $d['id'] = $p['id'];
            $d['image_path'] = getImageUrl($p['image_path']);
            $d['price'] = $p['price'];
        }
        $enhancedDrinks[] = $d;
    }
    $parsedData['drinks'] = $enhancedDrinks;

    $_SESSION[$cacheKey] = $parsedData;
    echo json_encode(['success' => true, 'data' => $parsedData]);
    exit();
}

include __DIR__ . '/header.php';
?>

<style>
    .macro-card {
        background: linear-gradient(135deg, #fff9f5 0%, #fff 100%);
        border-left: 5px solid #ff6b35;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        margin-bottom: 30px;
        display: flex;
        justify-content: space-around;
        text-align: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    .macro-stat {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .macro-val {
        font-size: 2.5rem;
        font-weight: 800;
        color: #333;
    }
    .macro-lbl {
        font-size: 0.9rem;
        color: #777;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 1px;
    }
    .rec-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .rec-item {
        background: white;
        border-radius: 15px;
        border: 1px solid #eee;
        padding: 20px;
        transition: transform 0.2s, box-shadow 0.2s;
        text-align: left;
    }
    .rec-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        border-color: #ffe6dd;
    }
    .rec-img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 15px;
        background: #f8f9fa;
        color: #999;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }
    .rec-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: #222;
        margin: 0 0 10px 0;
    }
    .rec-reason {
        font-size: 0.95rem;
        color: #666;
        line-height: 1.5;
        margin-bottom: 15px;
    }
</style>

<div class="dashboard-container">
    <div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 900px; margin: 0 auto;">
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <div style="font-size: 2.5rem; color: #ff6b35;">✨</div>
            <h1 style="color: #333; margin: 0;">Your Daily Nutrition Plan</h1>
        </div>
        
        <?php if (empty($user['height']) || empty($user['weight'])): ?>
            <div style="background: #fff3cd; color: #856404; padding: 25px; border-radius: 15px; text-align: center; border: 1px dashed #ffeeba;">
                <h3 style="margin-top: 0; font-size: 1.4rem;">Missing Information</h3>
                <p style="margin-bottom: 20px; font-size: 1.1rem;">We need your age, weight, height, and gender to accurately calculate your daily caloric and carb needs and provide personalized recommendations.</p>
                <a href="edit_profile.php" class="btn-login" style="display: inline-block; text-decoration: none;">Update Your Profile</a>
            </div>
        <?php else: ?>

            <div id="ai-recs-container" style="min-height: 300px;">
                <div id="loading-recs" style="text-align: center; padding: 50px 20px; color: #666;">
                    <i class="fas fa-circle-notch fa-spin" style="font-size: 3rem; color: #ff6b35; margin-bottom: 20px;"></i>
                    <p style="font-size: 1.1rem; font-weight: 500;">Calculating your macros and picking the best flavors...</p>
                </div>
                <div id="recs-content" style="display: none;">
                    
                    <p id="rec-summary" style="font-size: 1.1rem; color: #555; margin-bottom: 25px;"></p>

                    <div class="macro-card">
                        <div class="macro-stat">
                            <span class="macro-val" id="val-bmi">--</span>
                            <span class="macro-lbl">BMI</span>
                        </div>
                        <div style="width: 1px; background: #ecc5b3; margin: 0 10px;"></div>
                        <div class="macro-stat">
                            <span class="macro-val" id="val-cals">--</span>
                            <span class="macro-lbl">Calories (kcal)</span>
                        </div>
                        <div style="width: 1px; background: #ecc5b3; margin: 0 10px;"></div>
                        <div class="macro-stat">
                            <span class="macro-val" id="val-carbs">--</span>
                            <span class="macro-lbl">Carbs (g)</span>
                        </div>
                        <div style="width: 1px; background: #ecc5b3; margin: 0 10px;"></div>
                        <div class="macro-stat">
                            <span class="macro-val" id="val-protein">--</span>
                            <span class="macro-lbl">Protein (g)</span>
                        </div>
                        <div style="width: 1px; background: #ecc5b3; margin: 0 10px;"></div>
                        <div class="macro-stat">
                            <span class="macro-val" id="val-fats">--</span>
                            <span class="macro-lbl">Fats (g)</span>
                        </div>
                    </div>

                    <h2 style="font-size: 1.4rem; color: #333; margin: 30px 0 15px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;">Recommended Foods 🍽️</h2>
                    <div class="rec-grid" id="grid-foods"></div>

                    <h2 style="font-size: 1.4rem; color: #333; margin: 30px 0 15px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;">Recommended Beverages 🥤</h2>
                    <div class="rec-grid" id="grid-drinks"></div>

                </div>
            </div>

            <script>
                function renderItem(item) {
                    let btnHtml = '';
                    let imgHtml = '<div class="rec-img">No Image Available</div>';
                    
                    if (item.id) {
                        imgHtml = `<img src="${item.image_path || 'assets/images/placeholder-300x300.png'}" class="rec-img" alt="${item.name}">`;
                        btnHtml = `<a href="product.php?id=${item.id}" class="btn-login" style="display:block; text-align:center; text-decoration:none; padding:8px 12px; font-size:0.9rem;">View item</a>`;
                    }
                    
                    let priceHtml = item.price ? `<div style="color: #27ae60; font-weight: 700; margin-bottom: 10px;">${item.price}</div>` : '';
                    
                    return `
                        <div class="rec-item">
                            ${imgHtml}
                            <h3 class="rec-name">${item.name}</h3>
                            <p class="rec-reason">${item.reason}</p>
                            ${priceHtml}
                            ${btnHtml}
                        </div>
                    `;
                }

                function loadRecs(force = false) {
                    document.getElementById('loading-recs').style.display = 'block';
                    document.getElementById('recs-content').style.display = 'none';

                    let url = 'recommendations.php?action=api';
                    if (force) url += '&force=1';

                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('loading-recs').style.display = 'none';
                            const rc = document.getElementById('recs-content');
                            rc.style.display = 'block';
                            
                            if (data.error) {
                                rc.innerHTML = '<div style="color: red; padding: 20px; background: #fff3cd; border-radius: 10px;">Error: ' + data.error + '</div>';
                            } else {
                                const d = data.data;
                                document.getElementById('rec-summary').textContent = d.advice_summary || '';
                                document.getElementById('val-bmi').textContent = d.bmi || '--';
                                document.getElementById('val-cals').textContent = d.daily_calories || '--';
                                document.getElementById('val-carbs').textContent = d.daily_carbs || '--';
                                document.getElementById('val-protein').textContent = d.daily_protein || '--';
                                document.getElementById('val-fats').textContent = d.daily_fats || '--';
                                
                                document.getElementById('grid-foods').innerHTML = d.foods.map(renderItem).join('');
                                document.getElementById('grid-drinks').innerHTML = d.drinks.map(renderItem).join('');
                            }
                        })
                        .catch(err => {
                            document.getElementById('loading-recs').style.display = 'none';
                            const rc = document.getElementById('recs-content');
                            rc.style.display = 'block';
                            rc.innerHTML = '<div style="color: #721c24; padding: 20px; background: #f8d7da; border-radius: 10px; text-align: center;"><strong>Oops!</strong> Failed to contact the AI service. Please try refreshing.</div>';
                        });
                }

                document.addEventListener('DOMContentLoaded', function() {
                    loadRecs();
                });
            </script>
        <?php endif; ?>
    </div>
</div>

</main>
</body>
</html>
