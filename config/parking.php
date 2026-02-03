<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Thermal Printer Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the thermal printer connection settings.
    | Supported connection types: browser, file, network, windows
    |
    | browser  - Uses browser's native print dialog (works on all platforms)
    | file     - Linux USB printer (e.g., /dev/usb/lp0)
    | network  - Network printer via IP address
    | windows  - Windows shared printer (e.g., smb://computer/printer or LPT1)
    |
    */

    'printer' => [
        'connection_type' => env('PRINTER_CONNECTION_TYPE', 'browser'),
        'path' => env('PRINTER_PATH', '/dev/usb/lp0'),
        'host' => env('PRINTER_HOST', '192.168.1.100'),
        'port' => env('PRINTER_PORT', 9100),
    ],

];
