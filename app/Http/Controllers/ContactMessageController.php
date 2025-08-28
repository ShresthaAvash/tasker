<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\User;
use App\Mail\NewContactMessageMail;
use App\Notifications\NewContactMessageReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class ContactMessageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $contactMessage = ContactMessage::create($validated);

        // Find all super admins
        $superAdmins = User::where('type', 'S')->get();

        if ($superAdmins->isNotEmpty()) {
            // Send database notification
            Notification::send($superAdmins, new NewContactMessageReceived($contactMessage));

            // Send email notification to each super admin
            foreach ($superAdmins as $admin) {
                Mail::to($admin->email)->send(new NewContactMessageMail($contactMessage));
            }
        }

        return redirect()->route('landing')->with('success', 'Your message has been sent successfully!');
    }
}