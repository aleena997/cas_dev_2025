<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use Google_Client;
use Google_Service_Calendar;
use App\Models\UserModel;
use Twilio\Rest\Client as TwilioClient;

class CronController extends Controller
{
    public function eventReminder()
    {
        $userModel = new UserModel();
        $users = $userModel->table('users')->get()->getResult();

        foreach ($users as $user) {
            if (empty($user->access_token) || empty($user->phone)) {
                continue;
            }

            $client = new Google_Client();
            $client->setClientId(env('GOOGLE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));

            $client->setAccessToken(json_decode($user->access_token, true));

            if ($client->isAccessTokenExpired()) {
                // Token expired: optionally refresh or skip
                continue;
            }

            $calendarService = new Google_Service_Calendar($client);

            $now = date('c');
            $fiveMinLater = date('c', strtotime('+5 minutes'));

            try {
                $events = $calendarService->events->listEvents('primary', [
                    'timeMin' => $now,
                    'timeMax' => $fiveMinLater,
                    'singleEvents' => true,
                    'orderBy' => 'startTime'
                ]);

                foreach ($events->getItems() as $event) {
                    // Send call via Twilio
                    $this->sendCall($user->phone, $event->getSummary(), $event->getStart()->getDateTime());
                }

            } catch (\Exception $e) {
                log_message('error', 'Google Calendar Error: ' . $e->getMessage());
            }
        }
    }

    private function sendCall($toPhone, $eventTitle, $eventTime)
    {
        $twilioSid = env('TWILIO_SID');
        $twilioToken = env('TWILIO_AUTH_TOKEN');
        $twilioFrom = env('TWILIO_PHONE');
        $client = new TwilioClient($twilioSid, $twilioToken);

        $message = "Reminder. You have an event titled $eventTitle at $eventTime.";

        try {
            $call = $client->calls->create(
                $toPhone,
                $twilioFrom,
                [
                    'twiml' => "<Response><Say>$message</Say></Response>"
                ]
            );

            log_message('info', "Call triggered to $toPhone for event '$eventTitle'");
        } catch (\Exception $e) {
            log_message('error', 'Twilio Call Error: ' . $e->getMessage());
        }
    }
}
