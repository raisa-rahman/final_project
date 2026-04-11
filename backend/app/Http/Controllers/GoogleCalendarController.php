<?php

namespace App\Http\Controllers;

use Google\Client;
use Google\Service\Calendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * GoogleCalendarController manages the OAuth2 flow for synchronising
 * with Google Calendar services.
 */

class GoogleCalendarController extends Controller
{
    /**
     * Initialise and configure the Google API client.
     * * Sets up the credentials, redirect URIs, and the required scopes
     * for reading and managing calendar events.
     */
    private function getClient()
    {
        $client = new Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        // Define scopes: read-only access to calendars and full access to events
        $client->addScope(Calendar::CALENDAR_READONLY);
        $client->addScope(Calendar::CALENDAR_EVENTS);
        // 'offline' access type ensures we receive a refresh token to maintain access
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        return $client;
    }

    /**
     * Generate the Google authorisation URL.
     * * Returns the URL as a JSON response so the frontend can redirect 
     * the user to the Google sign-in page.
     */

    public function redirectToGoogle()
    {
        $client = $this->getClient();
        return response()->json(['url' => $client->createAuthUrl()]);
    }

    /**
     * Handle the callback from Google after the user authorises the app.
     * * Exchanges the authorisation code for an access token and persists 
     * it to the mentor's profile for future API requests.
     */

    public function handleGoogleCallback(Request $request)
    {
        $client = $this->getClient();
        // Exchange the temporary code provided by Google for a permanent token
        $token = $client->fetchAccessTokenWithAuthCode($request->code);
        // Retrieve the authenticated user's mentor profile and store the token
        $mentor = Auth::user()->mentorProfile;
        $mentor->update([
            'google_access_token' => json_encode($token),
        ]);
        // Redirect back to the frontend dashboard with a success status parameter
        return redirect('http://localhost:5173/dashboard?status=google_success');
    }
}