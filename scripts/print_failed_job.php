<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = $app->make('db');
$job = $db->table('failed_jobs')->orderBy('failed_at', 'desc')->first();
if (!$job) {
    echo "No failed jobs found\n";
    exit(0);
}

echo "id: " . $job->id . "\n";
echo "connection: " . $job->connection . "\n";
echo "queue: " . $job->queue . "\n";
echo "failed_at: " . $job->failed_at . "\n\n";

echo "exception:\n";
echo $job->exception . "\n\n";

echo "payload (trimmed):\n";
echo substr($job->payload, 0, 4000) . "\n";
