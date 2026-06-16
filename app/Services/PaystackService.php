<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PaystackService
{
    public function mode(): string
    {
        return (string) config('services.paystack.mode', 'test');
    }

    public function publicKey(): ?string
    {
        return $this->mode() === 'live'
            ? config('services.paystack.live_public_key')
            : config('services.paystack.test_public_key');
    }

    public function secretKey(): ?string
    {
        return $this->mode() === 'live'
            ? config('services.paystack.live_secret_key')
            : config('services.paystack.test_secret_key');
    }

    public function client(): PendingRequest
    {
        $secret = $this->secretKey();

        if (! $secret) {
            throw ValidationException::withMessages([
                'paystack' => 'Paystack secret key is not configured for the current mode.',
            ]);
        }

        return Http::baseUrl((string) config('services.paystack.base_url'))
            ->acceptJson()
            ->asJson()
            ->withToken($secret);
    }

    /**
     * @return array{authorization_url: string, access_code: string, reference: string}
     */
    public function initialize(array $payload): array
    {
        $response = $this->client()->post('/transaction/initialize', $payload);

        if (! $response->successful() || ! $response->json('status')) {
            throw ValidationException::withMessages([
                'paystack' => $response->json('message') ?? 'Unable to initialize Paystack payment.',
            ]);
        }

        return [
            'authorization_url' => (string) $response->json('data.authorization_url'),
            'access_code' => (string) $response->json('data.access_code'),
            'reference' => (string) $response->json('data.reference'),
        ];
    }

    public function verify(string $reference): array
    {
        $response = $this->client()->get('/transaction/verify/'.rawurlencode($reference));

        if (! $response->successful() || ! $response->json('status')) {
            throw ValidationException::withMessages([
                'paystack' => $response->json('message') ?? 'Unable to verify Paystack transaction.',
            ]);
        }

        return (array) $response->json('data');
    }

    public function computeWebhookSignature(string $payload): string
    {
        $secret = (string) $this->secretKey();

        return hash_hmac('sha512', $payload, $secret);
    }
}

