<?php
require_once __DIR__ . '/config.php';

// BMI Calculator Function
function calculateBMI($weight, $height)
{
    // Convert height from cm to meters
    $heightInMeters = $height / 100;
    $bmi = $weight / ($heightInMeters * $heightInMeters);
    return round($bmi, 1);
}

// Get BMI Category and nutritional recommendations
function getBMIRecommendations($bmi, $age, $gender, $activityLevel)
{
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
function calculateBMR($weight, $height, $age, $gender)
{
    if ($gender === 'male') {
        return (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
    } else {
        return (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
    }
}

// Calculate daily calories based on activity level
function calculateDailyCalories($bmr, $activityLevel)
{
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
function calculateMacronutrients($calories, $proteinMultiplier, $carbsMultiplier, $fatsMultiplier)
{
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

// Process form submission
$results = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weight = floatval($_POST['weight']);
    $height = floatval($_POST['height']);
    $age = intval($_POST['age']);
    $gender = $_POST['gender'];
    $activityLevel = $_POST['activity_level'];

    // Calculate BMI and nutritional needs
    $bmi = calculateBMI($weight, $height);
    $bmr = calculateBMR($weight, $height, $age, $gender);
    $baseCalories = calculateDailyCalories($bmr, $activityLevel);

    $bmiRecs = getBMIRecommendations($bmi, $age, $gender, $activityLevel);
    $adjustedCalories = $baseCalories + $bmiRecs['calories_adjustment'];
    $macros = calculateMacronutrients($adjustedCalories, $bmiRecs['protein_multiplier'], $bmiRecs['carbs_multiplier'], $bmiRecs['fats_multiplier']);

    $results = [
        'bmi' => $bmi,
        'category' => $bmiRecs['category'],
        'calories' => $adjustedCalories,
        'protein' => $macros['protein'],
        'carbs' => $macros['carbs'],
        'fats' => $macros['fats'],
        'base_calories' => $baseCalories,
        'calories_adjustment' => $bmiRecs['calories_adjustment']
    ];
}

include __DIR__ . '/header.php';
?>

<style>
    .bmi-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }

    .bmi-form {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-group input,
    .form-group select {
        padding: 12px 16px;
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #ff6b35;
    }

    .btn-calculate {
        background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
        color: white;
        border: none;
        padding: 14px 32px;
        border-radius: 25px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        display: block;
        margin: 0 auto;
    }

    .btn-calculate:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.3);
    }

    .results-card {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        text-align: center;
    }

    .bmi-value {
        font-size: 4rem;
        font-weight: 800;
        color: #ff6b35;
        margin: 20px 0;
    }

    .bmi-category {
        font-size: 1.8rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 30px;
    }

    .macros-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }

    .macro-item {
        background: linear-gradient(135deg, #fff9f5 0%, #fff 100%);
        border-left: 5px solid #ff6b35;
        border-radius: 15px;
        padding: 20px;
        text-align: center;
    }

    .macro-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #333;
        display: block;
    }

    .macro-label {
        font-size: 0.9rem;
        color: #777;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 1px;
    }

    .category-underweight {
        border-left-color: #3498db;
    }

    .category-normal {
        border-left-color: #27ae60;
    }

    .category-overweight {
        border-left-color: #f39c12;
    }

    .category-obese {
        border-left-color: #e74c3c;
    }
</style>

<div class="bmi-container">
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="color: #333; font-size: 2.5rem; margin-bottom: 10px;">BMI Calculator</h1>
        <p style="color: #666; font-size: 1.1rem;">Calculate your BMI and get personalized nutritional recommendations
        </p>
    </div>

    <div class="bmi-form">
        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group">
                    <label for="weight">Weight (kg)</label>
                    <input type="number" id="weight" name="weight" step="0.1" required placeholder="70.5">
                </div>

                <div class="form-group">
                    <label for="height">Height (cm)</label>
                    <input type="number" id="height" name="height" required placeholder="175">
                </div>

                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" required placeholder="25">
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="activity_level">Activity Level</label>
                    <select id="activity_level" name="activity_level" required>
                        <option value="">Select Activity Level</option>
                        <option value="sedentary">Sedentary (little or no exercise)</option>
                        <option value="lightly_active">Lightly Active (1-3 days/week)</option>
                        <option value="moderately_active">Moderately Active (3-5 days/week)</option>
                        <option value="very_active">Very Active (6-7 days/week)</option>
                        <option value="extra_active">Extra Active (very hard exercise)</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-calculate">Calculate BMI & Nutrition</button>
        </form>
    </div>

    <?php if ($results): ?>
        <div class="results-card">
            <h2 style="color: #333; margin-bottom: 20px;">Your Results</h2>

            <div class="bmi-value"><?php echo $results['bmi']; ?></div>
            <div class="bmi-category category-<?php echo strtolower(str_replace(' ', '-', $results['category'])); ?>">
                <?php echo $results['category']; ?>
            </div>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
                <p style="margin: 0; color: #666; font-size: 1rem;">
                    <?php if ($results['calories_adjustment'] > 0): ?>
                        Your daily calories have been increased by <?php echo abs($results['calories_adjustment']); ?> kcal to
                        support healthy weight gain.
                    <?php elseif ($results['calories_adjustment'] < 0): ?>
                        Your daily calories have been reduced by <?php echo abs($results['calories_adjustment']); ?> kcal to
                        support healthy weight loss.
                    <?php else: ?>
                        Your daily calories are optimized for weight maintenance.
                    <?php endif; ?>
                </p>
            </div>

            <h3 style="color: #333; margin-bottom: 20px;">Daily Nutritional Targets</h3>

            <div class="macros-grid">
                <div class="macro-item">
                    <span class="macro-value"><?php echo $results['calories']; ?></span>
                    <span class="macro-label">Calories (kcal)</span>
                </div>

                <div class="macro-item">
                    <span class="macro-value"><?php echo $results['protein']; ?></span>
                    <span class="macro-label">Protein (g)</span>
                </div>

                <div class="macro-item">
                    <span class="macro-value"><?php echo $results['carbs']; ?></span>
                    <span class="macro-label">Carbs (g)</span>
                </div>

                <div class="macro-item">
                    <span class="macro-value"><?php echo $results['fats']; ?></span>
                    <span class="macro-label">Fats (g)</span>
                </div>
            </div>

            <div
                style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 10px; border-left: 4px solid #ffc107;">
                <h4 style="color: #856404; margin-top: 0;">BMI Categories</h4>
                <div style="text-align: left; color: #856404; font-size: 0.95rem;">
                    <div>Underweight: BMI < 18.5</div>
                            <div>Normal weight: BMI 18.5-24.9</div>
                            <div>Overweight: BMI 25-29.9</div>
                            <div>Obese: BMI >= 30</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    </main>
    </body>

    </html>