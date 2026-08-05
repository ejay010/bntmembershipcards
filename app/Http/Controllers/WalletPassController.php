<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Services\MembershipCardService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WalletPassController extends Controller
{
    public function __construct(protected MembershipCardService $cardService) {}

    /**
     * Download Apple Wallet Pass (.pkpass)
     */
    public function applePass(string $uuid): BinaryFileResponse
    {
        $member = Member::where('uuid', $uuid)->firstOrFail();

        $passPath = $this->cardService->generateApplePass($member);

        $member->update([
            'apple_wallet_downloaded_at' => now(),
        ]);

        return response()->download($passPath, "{$member->membership_number}.pkpass", [
            'Content-Type' => 'application/vnd.apple.pkpass',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Redirect to Google Wallet Pass save link or show mock landing page
     */
    public function googlePass(Request $request, string $uuid)
    {
        $member = Member::where('uuid', $uuid)->firstOrFail();

        $member->update([
            'google_wallet_downloaded_at' => now(),
        ]);

        $googleUrl = $this->cardService->generateGoogleWalletUrl($member);

        // If it's a dev fallback and mock parameter is present, show a mock page
        if ($request->has('mock') || ! config('services.google.issuer_id')) {
            return view('pages.passes.google-mock', [
                'member' => $member,
            ]);
        }

        return redirect()->away($googleUrl);
    }
}
