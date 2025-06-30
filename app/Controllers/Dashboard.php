<?php
namespace App\Controllers;
use Google_Client;
use Google_Service_Calendar;
use App\Models\UserModel;

class Dashboard extends BaseController
{
  public function index()
  {
    $session = session();
    $user = $session->get('user'); // get user from session
    $refreshToken = $user['refresh_token'];

    // Initialize Google Client
    $client = new \Google_Client();
    $client->setClientId(env('GOOGLE_CLIENT_ID'));
    $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
    $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));   
    $client->addScope(Google_Service_Calendar::CALENDAR_READONLY);


    // Use refresh token to get access token
    $client->fetchAccessTokenWithRefreshToken($refreshToken);
    // Access Google Calendar API
    $calendarService = new Google_Service_Calendar($client);
    $now = date('c'); // RFC3339 format
    $optParams = [
        'maxResults' => 10,
        'orderBy' => 'startTime',
        'singleEvents' => true,
        'timeMin' => $now,
    ];

    $events = $calendarService->events->listEvents('primary', $optParams);
    $data['user'] = $user;
    $data['events'] = $events->getItems(); // pass events to view
    return view('dashboard', $data);
  }
  public function updatePhone()
  {
    $session = session();
    $user = $session->get('user');

    if (!$user) {
        return redirect()->to('/login');
    }

    $phone = $this->request->getPost('phone');

    if (!$phone) {
        return redirect()->back()->with('error', 'Phone number is required');
    }

    $userModel = new UserModel();
    $userModel->update($user['id'], ['phone' => esc($phone)]);

    // Update session
    $user['phone'] = $phone;
    $session->set('user', $user);

    return redirect()->back()->with('message', 'Phone number updated successfully');
  }

}


