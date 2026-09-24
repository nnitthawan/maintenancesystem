<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineService
{
    protected string $token;

    public function __construct()
    {
        $this->token = config('services.line.token');
    }

    /**
     * ส่งข้อความแบบ Push Message ไปยัง User ID หรือ Group ID
     */
    public function sendPush(string $to, string $message): bool
    {
        try {
            $response = Http::withToken($this->token)
                ->post('https://api.line.me/v2/bot/message/push', [
                    'to' => $to,
                    'messages' => [
                        [
                            'type' => 'text',
                            'text' => $message,
                        ],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('LINE Push Error: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('LINE Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ส่งข้อความแบบ Flex Message สวยงาม
     */
    public function sendFlex(string $to, string $altText, array $flexContents): bool
    {
        try {
            $response = Http::withToken($this->token)
                ->post('https://api.line.me/v2/bot/message/push', [
                    'to' => $to,
                    'messages' => [
                        [
                            'type' => 'flex',
                            'altText' => $altText,
                            'contents' => $flexContents,
                        ],
                    ],
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('LINE Flex Error: ' . $e->getMessage());
            return false;
        }
    }
}
