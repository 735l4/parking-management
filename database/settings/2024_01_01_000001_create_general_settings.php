<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.business_name', 'Parking Management System');
        $this->migrator->add('general.pan_number', '');
        $this->migrator->add('general.address', '');
        $this->migrator->add('general.logo', null);
        $this->migrator->add('general.phone_number', '');
    }
};
