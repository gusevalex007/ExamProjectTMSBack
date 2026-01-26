<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Project;
use Illuminate\Http\Request;

class TmaAuthController extends Controller
{
    public function auth(Request $request)
{
    $validated = $request->validate([
        'project_id' => 'required|integer|exists:projects,id',
        'init_data'  => 'nullable|string',
    ]);

    $project = Project::findOrFail($validated['project_id']);
    $initData = $validated['init_data'] ?? '';

    // Если local и init_data содержит "mock_hash" — пропускаем валидацию
    if (app()->environment('local') && str_contains($initData, 'mock_hash')) {
        // Парсим user из init_data (как в реальном Telegram)
        parse_str($initData, $params);
        $userJson = $params['user'] ?? null;
        
        if ($userJson) {
            $tgUser = json_decode($userJson, true);
        } else {
            // Фоллбэк если фронт не передал user
            $tgUser = [
                'id' => 123456789,
                'first_name' => 'Dev',
                'last_name' => 'User',
                'username' => 'devuser',
            ];
        }
    } elseif (app()->environment('local')) {
        // local, но init_data пустой или невалидный — хардкод
        $tgUser = [
            'id' => 123456789,
            'first_name' => 'Dev',
            'last_name' => 'User',
            'username' => 'devuser',
        ];
    } else {
        // Production: строгая проверка подписи Telegram
        if (!$this->validateInitData($initData, $project->bot_token)) {
            return response()->json(['message' => 'Invalid initData'], 401);
        }

        parse_str($initData, $params);

        $userJson = $params['user'] ?? null;
        if (!$userJson) {
            return response()->json(['message' => 'No user in initData'], 422);
        }

        $tgUser = json_decode($userJson, true);
        if (!$tgUser || empty($tgUser['id'])) {
            return response()->json(['message' => 'Invalid user payload'], 422);
        }
    }

    // Найти или создать customer
    $customer = Customer::where('project_id', $project->id)
        ->where('telegram_id', (int)$tgUser['id'])
        ->first();

    if (!$customer) {
        $customer = $project->customers()->create([
            'telegram_id' => (int)$tgUser['id'],
            'name' => trim(($tgUser['first_name'] ?? '') . ' ' . ($tgUser['last_name'] ?? '')),
            'username' => $tgUser['username'] ?? null,
            'orders_count' => 0,
            'total_spent' => 0,
        ]);
    }

    $token = $customer->createToken('tma')->plainTextToken;

    return response()->json([
        'token' => $token,
        'customer' => $customer,
    ]);
}

    

    private function validateInitData(string $initData, string $botToken): bool
    {
        parse_str($initData, $params);

        $hash = $params['hash'] ?? null;
        $authDate = $params['auth_date'] ?? null;

        if (!$hash || !$authDate) return false;

        // Защита от replay: отклонять слишком старые данные (можно поменять окно)
        // Telegram рекомендует проверять auth_date на свежесть. [web:72]
        if (time() - (int)$authDate > 3600) {
            return false;
        }

        unset($params['hash']);

        ksort($params);
        $pairs = [];
        foreach ($params as $k => $v) {
            $pairs[] = $k . '=' . $v;
        }
        $dataCheckString = implode("\n", $pairs);

        // secret_key = HMAC_SHA256(bot_token, "WebAppData") [web:72][web:146]
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($hash, $calculatedHash);
    }
}
