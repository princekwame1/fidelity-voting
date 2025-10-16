<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class QRCodeController extends Controller
{
    public function __construct(
        private QRCodeService $qrCodeService
    ) {}

    public function index(Event $event): View
    {
        // Get voting sessions for this event (replacing vote tokens)
        $sessions = $event->votingSessions()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Generate the single QR code URL for this event
        $qrCodeUrl = url('/vote/event/' . $event->encrypted_id);

        return view('admin.qrcodes.index', compact('event', 'sessions', 'qrCodeUrl'));
    }

    public function downloadSingle(Event $event): Response
    {
        // Generate QR code for the single event URL
        $url = url('/vote/event/' . $event->encrypted_id);
        $qrCode = $this->qrCodeService->generateQRCodeSVG($url, 300);

        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="qr-code-event-' . $event->id . '.svg"');
    }

    public function downloadPng(Event $event): Response
    {
        // Generate PNG QR code for the single event URL
        $url = url('/vote/event/' . $event->encrypted_id);

        // Use SimpleSoftwareIO QR Code to generate PNG
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(300)
            ->generate($url);

        return response($qrCode)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qr-code-event-' . $event->encrypted_id . '.png"');
    }

    public function downloadSheet(Event $event): Response
    {
        // Generate printable QR code sheet with event information
        $url = url('/vote/event/' . $event->encrypted_id);
        $qrCodeSvg = $this->qrCodeService->generateQRCodeSVG($url, 200);

        $html = $this->generatePrintableSheet($event, $qrCodeSvg, $url);

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="event-qr-sheet-' . $event->encrypted_id . '.html"');
    }

    public function preview(Event $event): Response
    {
        // Generate QR code preview for the event
        $url = url('/vote/event/' . $event->encrypted_id);

        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(200)
            ->generate($url);

        return response($qrCode)
            ->header('Content-Type', 'image/png');
    }

    private function generatePrintableSheet(Event $event, string $qrCodeSvg, string $url): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>QR Code - ' . htmlspecialchars($event->name) . '</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                .qr-container { margin: 20px auto; padding: 20px; border: 2px solid #000; max-width: 400px; }
                .qr-code { margin: 20px 0; }
                .event-info { margin: 10px 0; }
                .url { word-break: break-all; font-size: 12px; margin: 10px 0; }
                h1 { color: #333; }
                .instructions { background: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 5px; }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="qr-container">
                <h1>' . htmlspecialchars($event->name) . '</h1>

                <div class="event-info">
                    <p><strong>Event:</strong> ' . htmlspecialchars($event->name) . '</p>
                    <p><strong>Start:</strong> ' . $event->start_time->format('M d, Y g:i A') . '</p>
                    <p><strong>End:</strong> ' . $event->end_time->format('M d, Y g:i A') . '</p>
                </div>

                <div class="qr-code">
                    ' . $qrCodeSvg . '
                </div>

                <div class="instructions">
                    <h3>How to Vote:</h3>
                    <ol style="text-align: left;">
                        <li>Scan the QR code with your phone camera</li>
                        <li>Follow the link to open the voting page</li>
                        <li>Answer all questions</li>
                        <li>Submit your vote</li>
                    </ol>
                </div>

                <div class="url">
                    <strong>Direct URL:</strong><br>
                    ' . htmlspecialchars($url) . '
                </div>
            </div>

            <div class="no-print">
                <button onclick="window.print()">Print This Page</button>
            </div>
        </body>
        </html>';
    }
}
