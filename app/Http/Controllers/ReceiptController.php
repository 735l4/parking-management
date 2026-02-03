<?php

namespace App\Http\Controllers;

use App\Models\ParkingTicket;
use App\Settings\GeneralSettings;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(ParkingTicket $ticket, GeneralSettings $settings): View
    {
        return view('receipts.print', [
            'ticket' => $ticket->load('vehicleType'),
            'settings' => $settings,
        ]);
    }
}
