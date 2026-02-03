<?php

namespace App\Helpers;

use Carbon\Carbon;
use RohanAdhikari\NepaliDate\NepaliDate;

class NepaliHelper
{
    /**
     * Nepali digit mapping.
     *
     * @var array<string, string>
     */
    protected static array $nepaliDigits = [
        '0' => '०',
        '1' => '१',
        '2' => '२',
        '3' => '३',
        '4' => '४',
        '5' => '५',
        '6' => '६',
        '7' => '७',
        '8' => '८',
        '9' => '९',
    ];

    /**
     * English digit mapping (reverse).
     *
     * @var array<string, string>
     */
    protected static array $englishDigits = [
        '०' => '0',
        '१' => '1',
        '२' => '2',
        '३' => '3',
        '४' => '4',
        '५' => '5',
        '६' => '6',
        '७' => '7',
        '८' => '8',
        '९' => '9',
    ];

    /**
     * Nepali months.
     *
     * @var array<int, string>
     */
    protected static array $nepaliMonths = [
        1 => 'बैशाख',
        2 => 'जेठ',
        3 => 'असार',
        4 => 'श्रावण',
        5 => 'भाद्र',
        6 => 'आश्विन',
        7 => 'कार्तिक',
        8 => 'मंसिर',
        9 => 'पौष',
        10 => 'माघ',
        11 => 'फाल्गुण',
        12 => 'चैत्र',
    ];

    /**
     * Convert English date to Nepali BS date using the package.
     */
    public static function toBS(Carbon|string $date): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        try {
            $nepaliDate = NepaliDate::fromAd($date->toDateTime());

            return $nepaliDate->toDateString();
        } catch (\Exception $e) {
            // Fallback to English date if conversion fails
            return $date->format('Y-m-d');
        }
    }

    /**
     * Convert Nepali BS date to English AD date using the package.
     */
    public static function toAD(string $bsDate): Carbon
    {
        try {
            $nepaliDate = NepaliDate::parse($bsDate);

            return Carbon::instance($nepaliDate->toAd());
        } catch (\Exception $e) {
            // Fallback - return current date if conversion fails
            return Carbon::now();
        }
    }

    /**
     * Format date as Nepali BS date with time.
     */
    public static function formatBSDateTime(Carbon|string $date, bool $useNepaliDigits = false): string
    {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        $bsDate = self::toBS($date);
        $time = $date->format('H:i');
        $formatted = "{$bsDate} {$time}";

        if ($useNepaliDigits) {
            return self::toNepaliDigits($formatted);
        }

        return $formatted;
    }

    /**
     * Convert English number to Nepali digits.
     */
    public static function toNepaliDigits(string|int|float $number): string
    {
        $number = (string) $number;

        return strtr($number, self::$nepaliDigits);
    }

    /**
     * Convert Nepali digits to English number.
     */
    public static function toEnglishDigits(string $number): string
    {
        return strtr($number, self::$englishDigits);
    }

    /**
     * Format currency in Nepali format.
     */
    public static function formatCurrency(float|int $amount, bool $useNepaliDigits = true): string
    {
        $formatted = number_format($amount, 2);

        if ($useNepaliDigits) {
            $formatted = self::toNepaliDigits($formatted);
        }

        return 'रु. '.$formatted;
    }

    /**
     * Format number in Nepali format.
     */
    public static function formatNumber(float|int $number, int $decimals = 0, bool $useNepaliDigits = true): string
    {
        $formatted = number_format($number, $decimals);

        if ($useNepaliDigits) {
            return self::toNepaliDigits($formatted);
        }

        return $formatted;
    }

    /**
     * Validate Nepali phone number (10 digits starting with 97, 98, or 96).
     */
    public static function isValidNepaliPhone(string $phone): bool
    {
        // Remove any spaces or dashes
        $phone = preg_replace('/[\s\-]/', '', $phone);

        // Convert Nepali digits to English if needed
        $phone = self::toEnglishDigits($phone);

        // Check if it's a valid 10-digit Nepali number
        return (bool) preg_match('/^(97|98|96)\d{8}$/', $phone);
    }

    /**
     * Format phone number for display.
     */
    public static function formatPhone(string $phone, bool $useNepaliDigits = false): string
    {
        $phone = preg_replace('/[\s\-]/', '', $phone);
        $phone = self::toEnglishDigits($phone);

        // Format as XXX-XXXXXXX
        $formatted = substr($phone, 0, 3).'-'.substr($phone, 3);

        if ($useNepaliDigits) {
            return self::toNepaliDigits($formatted);
        }

        return $formatted;
    }

    /**
     * Format datetime for display.
     */
    public static function formatDateTime(Carbon $datetime, bool $useNepaliDigits = true): string
    {
        $formatted = $datetime->format('Y-m-d H:i');

        if ($useNepaliDigits) {
            return self::toNepaliDigits($formatted);
        }

        return $formatted;
    }

    /**
     * Format time for display.
     */
    public static function formatTime(Carbon $datetime, bool $useNepaliDigits = true): string
    {
        $formatted = $datetime->format('H:i');

        if ($useNepaliDigits) {
            return self::toNepaliDigits($formatted);
        }

        return $formatted;
    }
}
