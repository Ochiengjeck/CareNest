<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ClearTestUsersCommand extends Command
{
    protected $signature = 'carenest:clear-test-users {--force : Skip confirmation}';

    protected $description = 'Clear all seeded test users (keeps roles and permissions)';

    protected array $testEmails = [
        'admin@carenest.test',
        'manager@carenest.test',
        'nurse@carenest.test',
        'caregiver@carenest.test',
        'supervisor@carenest.test',
        'newuser@carenest.test',
    ];

    public function handle(): int
    {
        $count = User::whereIn('email', $this->testEmails)->count();

        if ($count === 0) {
            $this->info('No test users found.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Delete {$count} test user(s)?")) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        User::whereIn('email', $this->testEmails)->delete();
        $this->info("Deleted {$count} test user(s).");

        return self::SUCCESS;
    }
}
