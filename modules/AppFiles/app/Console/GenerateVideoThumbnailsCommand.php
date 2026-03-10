<?php

namespace Modules\AppFiles\Console;

use Illuminate\Console\Command;
use Modules\AppFiles\Services\UploadFileService;

/**
 * This command is dispatched asynchronously via shell_exec() right after
 * a video is uploaded so the HTTP response stays instant.
 *
 * Usage (auto-dispatched): php artisan files:generate-thumbnails <base64-payload>
 *
 * Register in AppFilesServiceProvider:
 *   $this->commands([GenerateVideoThumbnailsCommand::class]);
 */
class GenerateVideoThumbnailsCommand extends Command
{
    protected $signature   = 'files:generate-thumbnails {payload}';
    protected $description = 'Generate video thumbnails asynchronously after upload';

    public function handle(UploadFileService $service): int
    {
        $raw = base64_decode($this->argument('payload'));

        if (!$raw) {
            $this->error('Invalid payload');
            return 1;
        }

        $data = json_decode($raw, true);

        if (!isset($data['id_secure'], $data['file_path'], $data['disk'])) {
            $this->error('Payload missing required fields');
            return 1;
        }

        $this->info("Generating thumbnails for: {$data['id_secure']}");

        $service->generateVideoThumbnails(
            $data['id_secure'],
            $data['file_path'],
            $data['disk']
        );

        $this->info('Done.');
        return 0;
    }
}
