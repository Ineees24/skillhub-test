<?php

namespace App\Services;

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

class ActivityLogService
{
    private ?\MongoDB\Database $db = null;

    public function __construct()
    {
        if (! class_exists(Client::class)) {
            return;
        }

        try {
            $client = new Client(config('mongodb.uri'));
            $this->db = $client->selectDatabase(config('mongodb.database'));
        } catch (\Throwable $e) {
            \Log::warning('MongoDB activity log: '.$e->getMessage());
            $this->db = null;
        }
    }

    public function isAvailable(): bool
    {
        return $this->db !== null;
    }

   
    public function logEvent(string $event, array $payload = []): bool
    {
        if ($this->db === null || $event === '') {
            return false;
        }

        $document = array_merge(
            [
                'event' => $event,
                'timestamp' => new UTCDateTime,
            ],
            $payload
        );

        try {
            $this->db->selectCollection('activity_logs')->insertOne($document);

            return true;
        } catch (\Throwable $e) {
            \Log::warning('MongoDB logEvent: '.$e->getMessage());

            return false;
        }
    }

 
    public function log(?int $userId, string $role, string $action, array $metadata = []): bool
    {
        if ($this->db === null || $action === '') {
            return false;
        }

        $document = [
            'user_id' => $userId,
            'role' => $role,
            'action' => $action,
            'timestamp' => new UTCDateTime,
            'metadata' => $metadata,
        ];

        try {
            $this->db->selectCollection('activity_logs')->insertOne($document);

            return true;
        } catch (\Throwable $e) {
            \Log::warning('MongoDB activity log insert: '.$e->getMessage());

            return false;
        }
    }

  
    public function getByUser(int $userId, int $limit = 20): array
    {
        if ($this->db === null) {
            return [];
        }

        try {
            $cursor = $this->db->selectCollection('activity_logs')->find(
                ['user_id' => $userId],
                [
                    'sort' => ['timestamp' => -1],
                    'limit' => $limit,
                ]
            );

            $out = [];
            foreach ($cursor as $doc) {
                $decoded = json_decode(json_encode($doc), true);
                if (is_array($decoded)) {
                    $out[] = $decoded;
                }
            }

            return $out;
        } catch (\Throwable $e) {
            \Log::warning('MongoDB activity log fetch: '.$e->getMessage());

            return [];
        }
    }
}
