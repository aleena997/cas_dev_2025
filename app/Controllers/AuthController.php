<?php

namespace App\Controllers;

use Google_Client;
use Google_Service_Oauth2;
use Google_Service_Calendar;
use App\Models\UserModel;
use GuzzleHttp\Client as GuzzleClient;

class AuthController extends BaseController
{   
    private function getGoogleClient()
    {
        $guzzleClient = new GuzzleClient([
            'verify' => 'C:/wamp64/bin/php/php8.1.31/extras/ssl/cacert.pem'
        ]);
        $client = new Google_Client();
        $client->setHttpClient($guzzleClient);
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->addScope('email');
        $client->addScope('profile');
        $client->addScope(Google_Service_Calendar::CALENDAR_READONLY);
        return $client;
    }
    
    public function login()
    {
        return view('login');
    }

    public function googleLogin()
    {
        $client = $this->getGoogleClient();
        return redirect()->to($client->createAuthUrl());
    }

    public function googleCallback()
    {
        if($this->request->getVar('code')){
            $code=$this->request->getVar('code');
            $client = $this->getGoogleClient();
            $token = $client->fetchAccessTokenWithAuthCode($code);
            if (!isset($token['access_token'])) {                
                return redirect()->to('/login')->with('error', 'Failed to authenticate');
            }
                     
            $client->setAccessToken($token);
            $oauth = new \Google_Service_Oauth2($client);
            $googleUserData = $oauth->userinfo->get();
            $userData = [
                'google_id'     => $googleUserData['id'],
                'email'         => $googleUserData['email'],
                'name'          => $googleUserData['name'],
                'picture'       => $googleUserData['picture'],
                'access_token'  => $token['access_token'],
                'refresh_token' => $token['access_token']
            ];
            $userModel = new UserModel();
            // Check if user already exists
            $existing = $userModel->where('email', $userData['email'])->first();
            if ($existing) {
                $userModel->update($existing['id'], $userData);
                $userId = $existing['id'];
            } else {
                $userId = $userModel->insert($userData);
            }
            
            // Store user session
            session()->set('user', array_merge($userData, ['id' => $userId]));

            return redirect()->to('/dashboard');
            
        }

        return redirect()->to('/login')->with('error', 'Google Authentication Failed');
    }
    public function googleCallback2()
    {
        $client = $this->getGoogleClient();
        $code = $this->request->getVar('code');
        if ($code) {
            $token = $client->fetchAccessTokenWithAuthCode($code);
              
            if (!isset($token['error'])) {                
                $client->setAccessToken($token);
                $oauth = new \Google_Service_Oauth2($client);
                $googleUserData = $oauth->userinfo->get();
                $userData = [
                    'google_id'     => $googleUserData['id'],
                    'email'         => $googleUserData['email'],
                    'name'          => $googleUserData['name'],
                    'picture'       => $googleUserData['picture'],
                    'access_token'  => $token['access_token'],
                    'refresh_token' => $token['access_token']
                ];
                $userModel = new UserModel();
                // Check if user already exists
                $existing = $userModel->where('email', $userData['email'])->first();
                if ($existing) {
                    $userModel->update($existing['id'], $userData);
                    $userId = $existing['id'];
                } else {
                    $userId = $userModel->insert($userData);
                }
               
                // Store user session
                session()->set('user', array_merge($userData, ['id' => $userId]));

                return redirect()->to('/dashboard');
            }
        }

        return redirect()->to('/login')->with('error', 'Google Authentication Failed');
    }

    public function dashboard()
    {
        $user = session()->get('user');
        if(!isset($user['email'])) {
            return redirect()->to('/login');
        }
        return redirect()->to('home');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
