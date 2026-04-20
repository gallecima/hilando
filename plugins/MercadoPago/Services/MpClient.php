<?php

namespace Plugins\MercadoPago\Services;

use App\Models\Plugin;
use MercadoPago\MercadoPagoConfig as MPConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;

class MpClient
{
    private array $cfg = [];

    public function __construct()
    {
        $p         = Plugin::where('slug','mercadopago')->first();
        $this->cfg = $p?->config ?? [];

        $token      = (string)($this->cfg['access_token'] ?? '');
        $integrator = (string)($this->cfg['integrator_id'] ?? '');

        MPConfig::setAccessToken($token);
        if ($integrator !== '') {
            MPConfig::setIntegratorId($integrator);
        }
    }

    public function createPreference(array $data): array
    {
        $client = new PreferenceClient();

        $amount = (float)($data['amount'] ?? 123.45);
        if ($amount <= 0) $amount = 123.45;

        $success = $data['success'] ?? ($this->cfg['success_url'] ?? null);
        $failure = $data['failure'] ?? ($this->cfg['failure_url'] ?? null);
        $pending = $data['pending'] ?? ($this->cfg['pending_url'] ?? null);

        $appUrl = rtrim(config('app.url', ''), '/');
        if (!$success && $appUrl) $success = $appUrl . '/checkout/complete?status=success';
        if (!$failure && $appUrl) $failure = $appUrl . '/checkout/complete?status=failure';
        if (!$pending && $appUrl) $pending = $appUrl . '/checkout/complete?status=pending';

        $payload = [
            'items' => $data['items'] ?? [[
                'title'       => 'Pedido',
                'quantity'    => 1,
                'unit_price'  => $amount,
                'currency_id' => 'ARS',
            ]],
            'back_urls' => array_filter([
                'success' => $success,
                'failure' => $failure,
                'pending' => $pending,
            ]),
            'notification_url'     => $data['webhook'] ?? route('mp.webhook'),
            'statement_descriptor' => (string) config('app.name'),
            'external_reference'   => (string) ($data['external_reference'] ?? ''),
        ];

        if ($this->isPublicUrl($success)) {
            $payload['auto_return'] = 'approved';
        }

        try {
            $pref = $client->create($payload);
            return [
                'id'                 => (string) ($pref->id ?? ''),
                'init_point'         => (string) ($pref->init_point ?? ''),
                'sandbox_init_point' => (string) ($pref->sandbox_init_point ?? ''),
            ];
        } catch (MPApiException $e) {
            \Log::error('[MP] API error al crear preferencia', [
                'status'   => $e->getApiResponse()->getStatusCode() ?? null,
                'response' => $e->getApiResponse()->getContent() ?? null,
                'message'  => $e->getMessage(),
                'payload'  => $payload,
            ]);
            throw new \RuntimeException('MercadoPago rechazó la solicitud.');
        }
    }

    public function getPayment(string|int $paymentId): array
    {
        $client = new PaymentClient();

        try {
            $p = $client->get((string)$paymentId);
            return [
                'id'                 => (string) ($p->id ?? ''),
                'status'             => (string) ($p->status ?? ''),
                'status_detail'      => (string) ($p->status_detail ?? ''),
                'external_reference' => (string) ($p->external_reference ?? ''),
                'transaction_amount' => (float)  ($p->transaction_amount ?? 0),
                'currency_id'        => (string) ($p->currency_id ?? ''),
                'payer'              => [
                    'id'    => $p->payer->id    ?? null,
                    'email' => $p->payer->email ?? null,
                ],
                'raw' => $p,
            ];
        } catch (MPApiException $e) {
            \Log::error('[MP] API error al consultar pago', [
                'status'    => $e->getApiResponse()->getStatusCode() ?? null,
                'response'  => $e->getApiResponse()->getContent() ?? null,
                'message'   => $e->getMessage(),
                'paymentId' => (string)$paymentId,
            ]);
            throw new \RuntimeException('No se pudo consultar el pago en MercadoPago.');
        }
    }

    private function isPublicUrl(?string $url): bool
    {
        if (!$url) return false;
        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['host'])) return false;

        $host = $parts['host'];
        if ($host === 'localhost') return false;
        if (preg_match('/(^|\.)local$/i', $host)) return false;
        if (preg_match('/^(127\.|0\.0\.0\.0)/', $host)) return false;

        return in_array(($parts['scheme'] ?? ''), ['http','https'], true);
    }
}