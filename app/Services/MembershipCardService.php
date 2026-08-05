<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Str;
use ZipArchive;

class MembershipCardService
{
    /**
     * Generate an Apple Wallet pass (.pkpass file path).
     */
    public function generateApplePass(Member $member): string
    {
        $tempDir = storage_path('app/temp_passes/'.Str::random(10));
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // 1. Create pass.json
        $passData = $this->buildPassJson($member);
        file_put_contents($tempDir.'/pass.json', json_encode($passData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // 2. Put logo and icon files
        $this->ensurePassImagesExist($tempDir);

        // 3. Create manifest.json (SHA-1 hashes of files in the directory)
        $manifest = [];
        $filesToHash = ['pass.json', 'icon.png', 'icon@2x.png', 'logo.png', 'logo@2x.png'];
        foreach ($filesToHash as $file) {
            if (file_exists($tempDir.'/'.$file)) {
                $manifest[$file] = sha1_file($tempDir.'/'.$file);
            }
        }
        file_put_contents($tempDir.'/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        // 4. Create signature (signed manifest.json)
        $signaturePath = $tempDir.'/signature';
        $certPath = config('services.apple.pass_certificate');
        $password = config('services.apple.pass_password');

        $signed = false;
        if ($certPath && file_exists($certPath)) {
            $certContent = file_get_contents($certPath);
            $pkey = null;
            $cert = null;

            if (Str::endsWith($certPath, '.p12')) {
                if (openssl_pkcs12_read($certContent, $certs, $password)) {
                    $pkey = $certs['pkey'];
                    $cert = $certs['cert'];
                }
            } else {
                $cert = openssl_x509_read($certContent);
                $pkey = openssl_pkey_get_private($certContent, $password);
            }

            if ($cert && $pkey) {
                $tempSign = tempnam(sys_get_temp_dir(), 'sig');
                if (openssl_pkcs7_sign(
                    $tempDir.'/manifest.json',
                    $tempSign,
                    $cert,
                    $pkey,
                    [],
                    OPENSSL_PKCS7_BINARY | OPENSSL_PKCS7_DETACHED
                )) {
                    $signatureSMIME = file_get_contents($tempSign);
                    $parts = explode("\n\n", $signatureSMIME);
                    if (isset($parts[3])) {
                        file_put_contents($signaturePath, base64_decode($parts[3]));
                        $signed = true;
                    }
                }
                if (file_exists($tempSign)) {
                    unlink($tempSign);
                }
            }
        }

        if (! $signed) {
            file_put_contents($signaturePath, 'MOCK_SIGNATURE');
        }

        // 5. ZIP everything into a .pkpass file
        $zipPath = storage_path('app/temp_passes/'.$member->uuid.'.pkpass');

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFile($tempDir.'/pass.json', 'pass.json');
            $zip->addFile($tempDir.'/manifest.json', 'manifest.json');
            $zip->addFile($tempDir.'/signature', 'signature');

            foreach (['icon.png', 'icon@2x.png', 'logo.png', 'logo@2x.png'] as $file) {
                if (file_exists($tempDir.'/'.$file)) {
                    $zip->addFile($tempDir.'/'.$file, $file);
                }
            }
            $zip->close();
        }

        // Clean up temporary directory
        foreach (scandir($tempDir) as $file) {
            if ($file !== '.' && $file !== '..') {
                unlink($tempDir.'/'.$file);
            }
        }
        rmdir($tempDir);

        return $zipPath;
    }

    /**
     * Build the Apple pass.json data dictionary.
     */
    protected function buildPassJson(Member $member): array
    {
        return [
            'formatVersion' => 1,
            'passTypeIdentifier' => config('services.apple.pass_type_identifier', 'pass.org.bahamasnationaltrust.member'),
            'teamIdentifier' => config('services.apple.team_identifier', 'BNTTEAMID123'),
            'serialNumber' => $member->membership_number,
            'organizationName' => 'Bahamas National Trust',
            'description' => 'Bahamas National Trust Membership Card',
            'logoText' => 'Bahamas National Trust',

            'foregroundColor' => 'rgb(255, 255, 255)',
            'backgroundColor' => 'rgb(0, 104, 56)',
            'labelColor' => 'rgb(240, 240, 240)',

            'barcodes' => [
                [
                    'format' => 'PKBarcodeFormatQR',
                    'message' => route('members.pass.apple', $member->uuid),
                    'messageEncoding' => 'iso-8859-1',
                    'altText' => $member->membership_number,
                ],
            ],

            'generic' => [
                'primaryFields' => [
                    [
                        'key' => 'name',
                        'label' => 'MEMBER',
                        'value' => $member->name,
                    ],
                ],
                'secondaryFields' => [
                    [
                        'key' => 'number',
                        'label' => 'MEMBER ID',
                        'value' => $member->membership_number,
                    ],
                    [
                        'key' => 'tier',
                        'label' => 'TIER',
                        'value' => strtoupper($member->tier),
                    ],
                ],
                'auxiliaryFields' => [
                    [
                        'key' => 'expires',
                        'label' => 'EXPIRES',
                        'value' => $member->expires_at ? $member->expires_at->format('M d, Y') : 'NEVER',
                    ],
                ],
            ],
        ];
    }

    /**
     * Ensure dummy image assets exist in the temporary directory so the pass loads.
     */
    protected function ensurePassImagesExist(string $dir): void
    {
        $logoPath = public_path('images/logo.png');
        $iconPath = public_path('images/icon.png');

        if (file_exists($logoPath)) {
            copy($logoPath, $dir.'/logo.png');
            copy($logoPath, $dir.'/logo@2x.png');
        } else {
            $transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
            file_put_contents($dir.'/logo.png', $transparentPng);
            file_put_contents($dir.'/logo@2x.png', $transparentPng);
        }

        if (file_exists($iconPath)) {
            copy($iconPath, $dir.'/icon.png');
            copy($iconPath, $dir.'/icon@2x.png');
        } else {
            $transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');
            file_put_contents($dir.'/icon.png', $transparentPng);
            file_put_contents($dir.'/icon@2x.png', $transparentPng);
        }
    }

    /**
     * Generate Google Wallet Pass Save Link (JWT)
     */
    public function generateGoogleWalletUrl(Member $member): string
    {
        $issuerId = config('services.google.issuer_id');
        $classId = config('services.google.class_id', 'BNTMemberClass');
        $privateKey = config('services.google.private_key');
        $clientEmail = config('services.google.client_email');

        if (! $issuerId || ! $privateKey || ! $clientEmail) {
            // Development fallback mock URL
            return route('members.pass.google', ['uuid' => $member->uuid]).'?mock=1';
        }

        $now = time();
        $payload = [
            'iss' => $clientEmail,
            'aud' => 'google',
            'typ' => 'savetowallet',
            'iat' => $now,
            'payload' => [
                'genericObjects' => [
                    [
                        'id' => "{$issuerId}.{$member->uuid}",
                        'classId' => "{$issuerId}.{$classId}",
                        'genericType' => 'GENERIC_MEMBERSHIP',
                        'cardTitle' => [
                            'defaultValue' => [
                                'language' => 'en',
                                'value' => 'Bahamas National Trust',
                            ],
                        ],
                        'subheader' => [
                            'defaultValue' => [
                                'language' => 'en',
                                'value' => 'MEMBER',
                            ],
                        ],
                        'header' => [
                            'defaultValue' => [
                                'language' => 'en',
                                'value' => $member->name,
                            ],
                        ],
                        'hexBackgroundColor' => '#006838',
                        'barcode' => [
                            'type' => 'QR_CODE',
                            'value' => route('members.pass.apple', $member->uuid),
                        ],
                        'textModulesData' => [
                            [
                                'header' => 'MEMBER ID',
                                'body' => $member->membership_number,
                            ],
                            [
                                'header' => 'TIER',
                                'body' => strtoupper($member->tier),
                            ],
                            [
                                'header' => 'EXPIRES',
                                'body' => $member->expires_at ? $member->expires_at->format('M d, Y') : 'NEVER',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $jwt = $this->encodeJwt($payload, $privateKey);

        return "https://pay.google.com/gp/v/save/{$jwt}";
    }

    /**
     * Helpers to encode a JSON Web Token using RS256
     */
    protected function encodeJwt(array $payload, string $privateKey): string
    {
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $segments = [];
        $segments[] = $this->base64UrlEncode(json_encode($header));
        $segments[] = $this->base64UrlEncode(json_encode($payload));

        $signingInput = implode('.', $segments);
        $signature = '';

        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    protected function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
