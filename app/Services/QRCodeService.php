<?php

namespace App\Services;

use App\Models\Event;
use App\Models\VoteToken;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;

class QRCodeService
{
    public function generateTokensForEvent(Event $event, int $quantity): array
    {
        $tokens = [];

        for ($i = 0; $i < $quantity; $i++) {
            $token = VoteToken::create([
                'event_id' => $event->id,
                'token' => VoteToken::generateUniqueToken(),
            ]);

            $tokens[] = $token;
        }

        return $tokens;
    }

    public function generateQRCodeUrl(VoteToken $token): string
    {
        $signedToken = $this->signToken($token->token);

        return URL::route('vote.show', [
            'token' => $signedToken,
            'event' => $token->event_id
        ]);
    }

    public function generateQRCode(VoteToken $token, int $size = 200): string
    {
        $url = $this->generateQRCodeUrl($token);

        return QrCode::size($size)
            ->format('png')
            ->generate($url);
    }

    public function generateQRCodeSVG(VoteToken $token, int $size = 200): string
    {
        $url = $this->generateQRCodeUrl($token);

        return QrCode::size($size)
            ->format('svg')
            ->generate($url);
    }

    public function verifySignedToken(string $signedToken): ?string
    {
        try {
            return Crypt::decrypt($signedToken);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function signToken(string $token): string
    {
        return Crypt::encrypt($token);
    }

    public function generatePrintableQRSheet(Event $event, $tokens, string $format = 'html'): string
    {
        $qrCodes = [];

        foreach ($tokens as $token) {
            $qrCodes[] = [
                'token' => $token,
                'qr_svg' => $this->generateQRCodeSVG($token, 150),
                'url' => $this->generateQRCodeUrl($token)
            ];
        }

        if ($format === 'html') {
            return $this->generateHTMLSheet($event, $qrCodes);
        }

        return '';
    }

    private function generateHTMLSheet(Event $event, array $qrCodes): string
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>QR Codes for ' . htmlspecialchars($event->name) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .qr-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
        .qr-item { text-align: center; page-break-inside: avoid; }
        .qr-code { margin: 10px 0; }
        .token-info { font-size: 12px; color: #666; margin-top: 10px; }
        @media print {
            .qr-grid { grid-template-columns: repeat(2, 1fr); }
            .qr-item { margin-bottom: 40px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . htmlspecialchars($event->name) . '</h1>
        <p>Voting QR Codes - Generated on ' . now()->format('Y-m-d H:i:s') . '</p>
    </div>
    <div class="qr-grid">';

        foreach ($qrCodes as $item) {
            $html .= '
        <div class="qr-item">
            <div class="qr-code">' . $item['qr_svg'] . '</div>
            <div class="token-info">
                <strong>Scan to Vote</strong><br>
                Token: ' . substr($item['token']->token, 0, 8) . '...<br>
                Event: ' . htmlspecialchars($event->name) . '
            </div>
        </div>';
        }

        $html .= '
    </div>
</body>
</html>';

        return $html;
    }
}