<?php
/**
 * Calcule la distance à vol d'oiseau (en kilomètres) entre deux
 * points GPS grâce à la formule de Haversine.
 *
 * @param float $lat1 Latitude du premier point
 * @param float $lon1 Longitude du premier point
 * @param float $lat2 Latitude du second point
 * @param float $lon2 Longitude du second point
 * @return float Distance en kilomètres
 */
function calculerDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $rayonTerre = 6371; // rayon moyen de la Terre en km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $rayonTerre * $c;
}