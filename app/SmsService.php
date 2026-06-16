<?php

namespace App;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;

class SmsService
{
    const LOG_CHANNEL = 'sms';

    public function Send($phone, $message)
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => env('SIGMA_KEY'),
                ])
                ->withBody(json_encode([
                    'recipient' => '+7' . $phone,
                    'type'      => 'sms',
                    'payload'   => ['sender' => 'SOME_SENDER', 'text' => $message],
                ]), 'application/json')
                ->post('https://online.sigmasms.ru/api/sendings');

        } catch (ConnectionException $e) {
            Log::channel(self::LOG_CHANNEL)->error('sms request timeout or connection error', [
                'phone' => $phone,
                'text'  => $message,
                'error' => $e->getMessage(),
            ]);
        } catch (RequestException $e) {
            Log::channel(self::LOG_CHANNEL)->error('sms request error', [
                'phone' => $phone,
                'text'  => $message,
                'error' => $e->getMessage(),
                'response' => optional($e->response)->body(),
            ]);
        } catch (\Exception $e) {
            Log::channel(self::LOG_CHANNEL)->error('Unexpected error while sending sms', [
                'phone' => $phone,
                'text'  => $message,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
