<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $business_name;

    public string $pan_number;

    public string $address;

    public ?string $logo;

    public string $phone_number;

    public static function group(): string
    {
        return 'general';
    }
}
