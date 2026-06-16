<?php

namespace App;

use App\Exceptions\EntityNotFound;
use App\Models\AppUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CardService
{
    const LOG_CHANNEL = 'card-service';

    public function getResponse($method, $body) : \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        $start = microtime(true);
        $exception = null;
        $response = null;

        try {
            $response = Http::withBasicAuth('ServiceClient', env('CARD_SERVICE_PASS'))
                ->withBody(json_encode($body), 'application/json')
                ->connectTimeout(3)
                ->timeout(5)
                ->post('SOME_URL' . $method);
        }
        catch (\Exception $e) {
            Log::channel(self::LOG_CHANNEL)->error('Error get data from loyalty service: ' . $e->getMessage(), [
                'method' => $method,
                'body' => $body,
            ]);
            $exception = $e;
        }

        $elapsed = microtime(true) - $start;

        Log::channel(self::LOG_CHANNEL)->info($method, [
            'elapsed' => $elapsed,
            'body' => $body,
            'status' => $response ? $response->status() : "",
            'response' => ($response && !$response->successful()) ? $response->body() : "",
            'exception' => $exception ? $exception->getMessage() : ""
        ]);

        if ($exception) {
            throw $exception;
        }

        return $response;
    }

    private function handleGenericErrors(\Illuminate\Http\Client\Response $response): \Exception
    {
        return match ($response->status()) {
            400     => new \Exception($response->json()['Message']),
            404     => new EntityNotFound("Пользователь не найден."),
            409     => new EntityNotFound("Пользователь не найден."),
            500     => new \Exception("Сервис временно недоступен."),
            default => new \Exception("Неизвестная серверная ошибка."),
        };
    }

    /**
     * @throws \Exception
     */
    private function getClientCard($body): AppUser
    {
        $response = $this->getResponse('getclientcard', $body);
        if ($response->successful()) {
            return AppUser::create((object)$response->json());
        }

        throw $this->handleGenericErrors($response);
    }

    /**
     * @throws \Exception
     */
    public function GetClientCardByPhone($phone) : AppUser
    {
        return $this->getClientCard([
            'phone' => $phone
        ]);
    }

    /**
     * @throws \Exception
     */
    public function GetClientCardById($cardId) : AppUser
    {
        return $this->getClientCard([
            'clientcardid' => $cardId
        ]);
    }

    /**
     * @throws \Exception
     */
    public function PostClientCardById($body): bool
    {
        $response = $this->getResponse('postclientcard', $body);
        if ($response->successful()) {
            return true;
        }

        throw $this->handleGenericErrors($response);
    }

    /**
     * @throws \Exception
     */
    public function NewClientCard($body): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return $this->getResponse('newclientcard', $body);
    }

    /**
     * @throws \Exception
     */
    public function GetSaleHistory($body): object
    {
        $response = $this->getResponse('GetSaleHistory', $body);
        if ($response->successful()) {
            return (object)$response->json();
        }

        throw $this->handleGenericErrors($response);
    }

    /**
     * @throws \Exception
     */
    public function InitiateAccountDeletion($body): bool
    {
        $response = $this->getResponse('InitiateAccountDeletion', $body);
        if ($response->successful()) {
            return true;
        }

        throw $this->handleGenericErrors($response);
    }

    /**
     * @throws \Exception
     */
    public function ConfirmAccountDeletion($body): object
    {
        $response = $this->getResponse('ConfirmAccountDeletion', $body);
        if ($response->successful()) {
            return (object)$response->json();
        }

        throw $this->handleGenericErrors($response);
    }

    public function UserLoggedNotification($body)
    {
        $response = $this->getResponse('CreateAccount', $body);

        if (!$response->successful()) {
            throw $response->toException();
        }
    }
}
