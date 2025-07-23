<?php

namespace App\Helpers;

class ColorHelper
{
    /**
     * Get the color code for a given color name
     */
    public static function getColorCode($color)
    {
        $colors = [
            'red' => '#dc3545',
            'blue' => '#007bff',
            'green' => '#28a745',
            'yellow' => '#ffc107',
            'orange' => '#fd7e14',
            'purple' => '#6f42c1',
            'pink' => '#e83e8c',
            'black' => '#343a40',
            'white' => '#f8f9fa',
            'gray' => '#6c757d',
            'grey' => '#6c757d',
            'brown' => '#795548',
            'silver' => '#c0c0c0',
            'gold' => '#ffd700',
            'golden' => '#ffd700',
            'clear' => '#e9ecef',
            'mixed' => '#6c757d'
        ];

        return $colors[strtolower($color)] ?? '#6c757d';
    }

    /**
     * Get the appropriate text color for a given background color
     */
    public static function getTextColor($color)
    {
        $darkColors = ['red', 'black', 'blue', 'purple', 'brown', 'gray', 'grey', 'mixed', 'green'];
        
        return in_array(strtolower($color), $darkColors) ? '#ffffff' : '#000000';
    }
}
