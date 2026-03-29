<?php

declare(strict_types=1);

namespace App\Central\ActivityLogModule\Actions;

use App\Central\ActivityLogModule\DTOs\GlobalLogEntryData;
use App\Central\ActivityLogModule\DTOs\ListGlobalLogsFilterData;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListGlobalLogsAction {
   private const MAX_READ_LINES = 3000;

   public function execute(ListGlobalLogsFilterData $filter): LengthAwarePaginator {
      $entries = collect($this->readEntries())
         ->filter(function (GlobalLogEntryData $entry) use ($filter): bool {
            if ($filter->tenantId !== null && $filter->tenantId !== '') {
               $matchesTenant = $entry->tenantId === $filter->tenantId
                  || str_contains($entry->raw, $filter->tenantId);

               if (! $matchesTenant) {
                  return false;
               }
            }

            if ($filter->level !== null && $filter->level !== '' && $entry->level !== $filter->level) {
               return false;
            }

            if ($filter->search !== '' && ! str_contains(strtolower($entry->raw), strtolower($filter->search))) {
               return false;
            }

            return true;
         })
         ->values();

      $total = $entries->count();
      $items = $entries
         ->forPage($filter->page, $filter->perPage)
         ->values();

      return new LengthAwarePaginator(
         items: $items,
         total: $total,
         perPage: $filter->perPage,
         currentPage: $filter->page,
         options: [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => $filter->pageName,
         ],
      );
   }

   /**
    * @return list<GlobalLogEntryData>
    */
   private function readEntries(): array {
      $path = (string) config('logging.channels.single.path', storage_path('logs/laravel.log'));

      if (! is_file($path)) {
         return [];
      }

      $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

      if (! is_array($lines) || $lines === []) {
         return [];
      }

      $lastLines = array_slice($lines, -self::MAX_READ_LINES);
      $parsed = [];

      foreach ($lastLines as $line) {
         $entry = $this->parseLine((string) $line);

         if ($entry !== null) {
            $parsed[] = $entry;
         }
      }

      return array_reverse($parsed);
   }

   private function parseLine(string $line): ?GlobalLogEntryData {
      if (! preg_match('/^\[(?<timestamp>[^\]]+)\]\s+\w+\.(?<level>[A-Z]+):\s(?<body>.+)$/', $line, $matches)) {
         return null;
      }

      $tenantId = $this->extractTenantId($matches['body']);

      $message = $matches['body'];
      $contextPosition = strpos($message, ' {');

      if ($contextPosition !== false) {
         $message = substr($message, 0, $contextPosition);
      }

      return new GlobalLogEntryData(
         timestamp: (string) $matches['timestamp'],
         level: strtolower((string) $matches['level']),
         message: trim($message),
         tenantId: $tenantId,
         raw: $line,
      );
   }

   private function extractTenantId(string $body): ?string {
      if (preg_match('/"tenant_id"\s*:\s*"([^"]+)"/', $body, $jsonMatch)) {
         return (string) $jsonMatch[1];
      }

      if (preg_match('/tenant[_-]?id[=: ]+([a-zA-Z0-9._-]+)/i', $body, $inlineMatch)) {
         return (string) $inlineMatch[1];
      }

      return null;
   }
}
