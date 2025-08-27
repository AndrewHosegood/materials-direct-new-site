<?php
/*
$setLength = 10 / 10; // 1
$setWidth = 4 / 10;   // 0.4
$cost_per_cm2 = 0.02061;
$totalSqMm = $setWidth * $setLength; // 0.4

function exponentialDecay($A, $k, $t) {
    return $A * exp(-$k * $t);
}

$A = 0.68;      // Maximum Cost Factor possible
$k = 0.0018;    // Decay Rate
$t = $totalSqMm; // mm2 of part

$costFactorResult = exponentialDecay($A, $k, $t);

echo "costFactorResult: " . $costFactorResult;
*/
?>


<?php
    $width = 4;
    $length = 10;
    $qty = 2000;

    $cost_per_cm2 = 0.02061;
    $item_border = 0.2;
    $globalPriceAdjust = 0.90;

    // Core calculation
    $borderSize = $item_border * 2;
    $setLength = $length / 10;
    $setWidth = $width / 10;
    $maxSetWidth = $setWidth + $borderSize;
    $maxSetLength = $setLength + $borderSize;
    $ppp = $maxSetLength * $maxSetWidth * $cost_per_cm2;
    //$ppp = 0.023083199999999998;
    $totalSqMm = $setWidth * $setLength;

    // Codys algorith
    function exponentialDecay($A, $k, $t) {
    return $A * exp(-$k * $t);
    }

    $A = 0.68;      // Maximum Cost Factor possible
    $k = 0.0018;    // Decay Rate
    $t = $totalSqMm; // mm2 of part

    $costFactorResult = exponentialDecay($A, $k, $t);
    // End Codys algorith

    $discountRate = 0; // Hardcoded for now
    $finalPppOnAva = $ppp + $costFactorResult;
    $discountAmount = $finalPppOnAva * $discountRate;
    $finalPppOnAva = $finalPppOnAva - $discountAmount;

    $adjustedPrice = $finalPppOnAva * $globalPriceAdjust;
    $total_price = $adjustedPrice * $qty;

    echo "cost_per_cm2: " . $cost_per_cm2 . "<br>";
    echo "maxSetLength: " . $maxSetLength . "<br>";
    echo "maxSetWidth: " . $maxSetWidth . "<br>";
    echo "ppp: " . $ppp . "<br>";
    echo "setWidth: " . $setWidth . "<br>";
    echo "setLength: " . $setLength . "<br>";
    echo "borderSize: " . $borderSize . "<br>";
    echo "costFactorResult: " . $costFactorResult . "<br>";
    echo "discountAmount: " . $discountAmount . "<br>";
    echo "finalPppOnAva: " . $finalPppOnAva . "<br>";
    echo "Total Price: " . $total_price;
?>