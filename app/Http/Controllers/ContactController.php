<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormSubmitted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Handle contact form submission and send the email to the support inbox.
     */
    public function send(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            Mail::to('dangthanhtuan8200@gmail.com')->send(new ContactFormSubmitted($data));
        } catch (\Throwable $exception) {
            Log::error('Contact form email failed', [
                'error' => $exception->getMessage(),
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Gửi liên hệ thất bại, vui lòng thử lại sau.',
                ], 500);
            }

            return back()
                ->withErrors(['message' => 'Gửi liên hệ thất bại, vui lòng thử lại sau.'])
                ->withInput();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Đã gửi liên hệ thành công. Chúng tôi sẽ phản hồi sớm.',
            ]);
        }

        return back()->with('status', 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm.');
    }
}
