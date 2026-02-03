<?php

namespace App\Services;

use App\Helpers\NepaliHelper;
use App\Models\ParkingTicket;
use App\Settings\GeneralSettings;
use Exception;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class ThermalPrinterService
{
    protected ?Printer $printer = null;

    protected GeneralSettings $settings;

    public function __construct(GeneralSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Check if browser printing is enabled.
     */
    public function isBrowserPrintEnabled(): bool
    {
        return config('parking.printer.connection_type') === 'browser';
    }

    /**
     * Get the printer connector based on configuration.
     */
    protected function getConnector()
    {
        $connectionType = config('parking.printer.connection_type', 'browser');
        $path = config('parking.printer.path', '/dev/usb/lp0');
        $host = config('parking.printer.host', '192.168.1.100');
        $port = config('parking.printer.port', 9100);

        return match ($connectionType) {
            'browser' => throw new Exception('BROWSER_PRINT'),
            'network' => new NetworkPrintConnector($host, $port),
            'windows' => new WindowsPrintConnector($path),
            default => new FilePrintConnector($path),
        };
    }

    /**
     * Initialize the printer connection.
     */
    protected function connect(): void
    {
        if ($this->printer === null) {
            $connector = $this->getConnector();
            $this->printer = new Printer($connector);
        }
    }

    /**
     * Close the printer connection.
     */
    protected function disconnect(): void
    {
        if ($this->printer !== null) {
            $this->printer->close();
            $this->printer = null;
        }
    }

    /**
     * Print a parking receipt.
     */
    public function printReceipt(ParkingTicket $ticket): void
    {
        try {
            $this->connect();

            // Header
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setEmphasis(true);
            $this->printer->setTextSize(2, 2);
            $this->printer->text($this->settings->business_name."\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->setEmphasis(false);

            if ($this->settings->address) {
                $this->printer->text($this->settings->address."\n");
            }

            if ($this->settings->phone_number) {
                $this->printer->text('Tel: '.NepaliHelper::formatPhone($this->settings->phone_number)."\n");
            }

            if ($this->settings->pan_number) {
                $this->printer->text('PAN: '.$this->settings->pan_number."\n");
            }

            $this->printer->feed();
            $this->printer->text("--------------------------------\n");
            $this->printer->text("PARKING RECEIPT\n");
            $this->printer->text("--------------------------------\n");
            $this->printer->feed();

            // Ticket Details
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);

            $this->printLine('Ticket No.', $ticket->ticket_number);
            $this->printLine('Vehicle No.', $ticket->vehicle_no);
            $this->printLine('Type', $ticket->vehicleType->name);
            $this->printLine('Phone', NepaliHelper::formatPhone($ticket->phone_number));

            $this->printer->feed();
            $this->printer->text("--------------------------------\n");

            // Time Details
            $this->printLine('In Time', $ticket->check_in->format('Y-m-d H:i'));

            if ($ticket->check_out) {
                $this->printLine('Out Time', $ticket->check_out->format('Y-m-d H:i'));
            }

            $this->printLine('Duration', $ticket->duration_for_display);

            $this->printer->text("--------------------------------\n");
            $this->printer->feed();

            // Total
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setEmphasis(true);
            $this->printer->setTextSize(2, 1);
            $this->printer->text('TOTAL: Rs. '.number_format($ticket->total_price, 2)."\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->setEmphasis(false);

            $this->printer->feed();
            $this->printer->text("--------------------------------\n");

            // Footer
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("Thank you for parking with us!\n");
            $this->printer->text(now()->format('Y-m-d H:i:s')."\n");

            $this->printer->feed(3);
            $this->printer->cut();

        } catch (Exception $e) {
            throw new Exception('Failed to print receipt: '.$e->getMessage());
        } finally {
            $this->disconnect();
        }
    }

    /**
     * Print a check-in slip.
     */
    public function printCheckInSlip(ParkingTicket $ticket): void
    {
        try {
            $this->connect();

            // Header
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setEmphasis(true);
            $this->printer->setTextSize(2, 2);
            $this->printer->text($this->settings->business_name."\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->setEmphasis(false);

            if ($this->settings->address) {
                $this->printer->text($this->settings->address."\n");
            }

            $this->printer->feed();
            $this->printer->text("--------------------------------\n");
            $this->printer->text("PARKING TICKET\n");
            $this->printer->text("--------------------------------\n");
            $this->printer->feed();

            // Ticket number in large font
            $this->printer->setTextSize(2, 2);
            $this->printer->setEmphasis(true);
            $this->printer->text($ticket->ticket_number."\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->setEmphasis(false);

            $this->printer->feed();
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);

            $this->printLine('Vehicle No.', $ticket->vehicle_no);
            $this->printLine('Type', $ticket->vehicleType->name);
            $this->printLine('In Time', $ticket->check_in->format('Y-m-d H:i'));

            $this->printer->feed();
            $this->printer->text("--------------------------------\n");

            // Rates
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("RATES\n");
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printLine('Hourly', 'Rs. '.number_format($ticket->vehicleType->hourly_rate, 2));
            $this->printLine('Minimum', 'Rs. '.number_format($ticket->vehicleType->minimum_charge, 2));

            $this->printer->feed();
            $this->printer->text("--------------------------------\n");

            // Footer
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("Please keep this ticket safe\n");
            $this->printer->text("for vehicle release.\n");

            $this->printer->feed(3);
            $this->printer->cut();

        } catch (Exception $e) {
            throw new Exception('Failed to print check-in slip: '.$e->getMessage());
        } finally {
            $this->disconnect();
        }
    }

    /**
     * Print a formatted line with label and value.
     */
    protected function printLine(string $label, string $value): void
    {
        $lineWidth = 32;
        $labelWidth = strlen($label) + 1;
        $valueWidth = $lineWidth - $labelWidth;

        $formattedValue = str_pad($value, $valueWidth, ' ', STR_PAD_LEFT);
        $this->printer->text($label.':'.$formattedValue."\n");
    }

    /**
     * Test the printer connection.
     */
    public function testConnection(): bool
    {
        try {
            $this->connect();
            $this->printer->text("Printer Test\n");
            $this->printer->text(now()->format('Y-m-d H:i:s')."\n");
            $this->printer->feed(2);
            $this->printer->cut();
            $this->disconnect();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
