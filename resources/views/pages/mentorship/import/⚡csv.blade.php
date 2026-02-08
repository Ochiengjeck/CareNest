<?php

use App\Models\MentorshipTopic;
use App\Concerns\MentorshipValidationRules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new
#[Layout('layouts.mentorship')]
#[Title('Import Topics from CSV')]
class extends Component {
    use MentorshipValidationRules, WithFileUploads;

    public $csv_file = null;
    public bool $overwrite_existing = false;
    public array $preview = [];
    public bool $showPreview = false;
    public int $importedCount = 0;

    public function uploadCsv(): void
    {
        $this->validate($this->mentorshipCsvUploadRules());

        // Read CSV and generate preview
        $path = $this->csv_file->getRealPath();
        $file = fopen($path, 'r');

        $this->preview = [];
        $header = fgetcsv($file); // Skip header row

        $rowCount = 0;
        while (($row = fgetcsv($file)) !== false && $rowCount < 10) {
            if (count($row) < 5) continue;

            $date = trim($row[0]);
            $day = trim($row[1]);

            $slots = [
                ['time' => '10:00', 'data' => trim($row[2] ?? '')],
                ['time' => '14:00', 'data' => trim($row[3] ?? '')],
                ['time' => '18:00', 'data' => trim($row[4] ?? '')],
            ];

            foreach ($slots as $slot) {
                if (empty($slot['data']) || $slot['data'] === '—' || $slot['data'] === '-') continue;

                // Parse "Title (Category)" format
                if (preg_match('/^(.+?)\s*\((.+?)\)$/', $slot['data'], $matches)) {
                    $title = trim($matches[1]);
                    $category = trim($matches[2]);

                    $this->preview[] = [
                        'date' => $date,
                        'day' => $day,
                        'time' => $slot['time'],
                        'title' => $title,
                        'category' => $category,
                    ];
                    $rowCount++;
                }
            }
        }

        fclose($file);
        $this->showPreview = true;
    }

    public function import(): void
    {
        $this->validate($this->mentorshipCsvUploadRules());

        $path = $this->csv_file->getRealPath();
        $file = fopen($path, 'r');

        $header = fgetcsv($file); // Skip header
        $this->importedCount = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 5) continue;

            $date = trim($row[0]);
            $day = trim($row[1]);

            // Parse date (handle various formats)
            try {
                $parsedDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
                $parsedDay = $day ?: \Carbon\Carbon::parse($date)->format('D');
            } catch (\Exception $e) {
                continue; // Skip invalid dates
            }

            $slots = [
                ['time' => '10:00', 'data' => trim($row[2] ?? '')],
                ['time' => '14:00', 'data' => trim($row[3] ?? '')],
                ['time' => '18:00', 'data' => trim($row[4] ?? '')],
            ];

            foreach ($slots as $slot) {
                if (empty($slot['data']) || $slot['data'] === '—' || $slot['data'] === '-') continue;

                // Parse "Title (Category)" format
                if (preg_match('/^(.+?)\s*\((.+?)\)$/', $slot['data'], $matches)) {
                    $title = trim($matches[1]);
                    $category = trim($matches[2]);

                    $data = [
                        'topic_date' => $parsedDate,
                        'day_of_week' => $parsedDay,
                        'time_slot' => $slot['time'],
                        'title' => $title,
                        'category' => $category,
                        'is_published' => true,
                        'created_by' => auth()->id(),
                    ];

                    if ($this->overwrite_existing) {
                        MentorshipTopic::updateOrCreate(
                            [
                                'topic_date' => $parsedDate,
                                'time_slot' => $slot['time'],
                            ],
                            $data
                        );
                    } else {
                        if (!MentorshipTopic::where('topic_date', $parsedDate)
                            ->where('time_slot', $slot['time'])
                            ->exists()) {
                            MentorshipTopic::create($data);
                        }
                    }

                    $this->importedCount++;
                }
            }
        }

        fclose($file);

        session()->flash('status', __(':count topics imported successfully!', ['count' => $this->importedCount]));
        $this->redirect(route('mentorship.topics.index'), navigate: true);
    }

    public function resetForm(): void
    {
        $this->csv_file = null;
        $this->preview = [];
        $this->showPreview = false;
        $this->overwrite_existing = false;
        $this->resetValidation();
    }
}; ?>

<flux:main>
    <div class="space-y-6 max-w-4xl">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Import Topics from CSV') }}</flux:heading>
                <flux:subheading>{{ __('Bulk upload weekly counseling topics') }}</flux:subheading>
            </div>

            <flux:button variant="ghost" :href="route('mentorship.topics.index')" wire:navigate icon="arrow-left">
                {{ __('Back') }}
            </flux:button>
        </div>

        {{-- CSV Format Instructions --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('CSV Format Instructions') }}</flux:heading>

            <div class="space-y-4">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Your CSV file should have the following structure:') }}
                </p>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead class="bg-zinc-100 dark:bg-zinc-800">
                            <tr>
                                <th class="border border-zinc-300 dark:border-zinc-600 p-2 text-left">{{ __('Date') }}</th>
                                <th class="border border-zinc-300 dark:border-zinc-600 p-2 text-left">{{ __('Day') }}</th>
                                <th class="border border-zinc-300 dark:border-zinc-600 p-2 text-left">{{ __('10:00 AM') }}</th>
                                <th class="border border-zinc-300 dark:border-zinc-600 p-2 text-left">{{ __('2:00 PM') }}</th>
                                <th class="border border-zinc-300 dark:border-zinc-600 p-2 text-left">{{ __('6:00 PM') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-zinc-300 dark:border-zinc-600 p-2">Feb 2</td>
                                <td class="border border-zinc-300 dark:border-zinc-600 p-2">Sun</td>
                                <td class="border border-zinc-300 dark:border-zinc-600 p-2 text-xs">DBT-INFORMED– Mindfulness (Mental Health)</td>
                                <td class="border border-zinc-300 dark:border-zinc-600 p-2 text-xs">Coping With Triggers (Mental Health)</td>
                                <td class="border border-zinc-300 dark:border-zinc-600 p-2 text-xs">Personal Intentions (Spirituality)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="space-y-2 text-sm">
                    <p class="font-semibold">{{ __('Format Rules:') }}</p>
                    <ul class="list-disc list-inside space-y-1 text-zinc-600 dark:text-zinc-400 ml-2">
                        <li>{{ __('First row should be headers (will be skipped)') }}</li>
                        <li>{{ __('Date column: Any standard date format (e.g., "Feb 2", "2026-02-02", "02/02/2026")') }}</li>
                        <li>{{ __('Day column: Day abbreviation (e.g., "Sun", "Mon", "Tue")') }}</li>
                        <li>{{ __('Time columns: Each cell should contain "Title (Category)"') }}</li>
                        <li>{{ __('Use "—" or leave empty for time slots without topics') }}</li>
                    </ul>
                </div>

                <div class="space-y-2 text-sm">
                    <p class="font-semibold">{{ __('Available Categories:') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <flux:badge color="blue">{{ __('Mental Health') }}</flux:badge>
                        <flux:badge color="purple">{{ __('Substance Use Disorder') }}</flux:badge>
                        <flux:badge color="green">{{ __('Employment/Education') }}</flux:badge>
                        <flux:badge color="red">{{ __('Physical Health') }}</flux:badge>
                        <flux:badge color="amber">{{ __('Financial/Housing') }}</flux:badge>
                        <flux:badge color="cyan">{{ __('Psycho-Social/Family') }}</flux:badge>
                        <flux:badge color="rose">{{ __('Spirituality') }}</flux:badge>
                    </div>
                </div>
            </div>
        </flux:card>

        {{-- Upload Form --}}
        @if(!$showPreview)
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Upload CSV File') }}</flux:heading>

            <form wire:submit="uploadCsv" class="space-y-4">
                <div>
                    <flux:label>{{ __('CSV File') }} <span class="text-red-500">*</span></flux:label>
                    <input
                        type="file"
                        wire:model="csv_file"
                        accept=".csv,.txt"
                        class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300"
                    >
                    @error('csv_file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <p class="text-xs text-zinc-500 mt-1">{{ __('Max 2MB. CSV or TXT format.') }}</p>
                </div>

                <flux:checkbox wire:model="overwrite_existing" :label="__('Overwrite existing topics (same date + time slot)')" />

                <flux:button type="submit" variant="primary">
                    {{ __('Preview Import') }}
                </flux:button>
            </form>
        </flux:card>
        @endif

        {{-- Preview --}}
        @if($showPreview)
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Preview (First 10 Topics)') }}</flux:heading>

            @if(empty($preview))
                <div class="text-center py-8">
                    <flux:icon.exclamation-triangle class="mx-auto h-12 w-12 text-amber-500" />
                    <p class="mt-4 text-zinc-500">{{ __('No valid topics found in the CSV file.') }}</p>
                    <p class="text-sm text-zinc-400">{{ __('Make sure each time slot cell contains "Title (Category)" format.') }}</p>
                </div>
            @else
                <div class="space-y-3 mb-6">
                    @foreach($preview as $item)
                        <div class="flex items-center gap-4 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800">
                            <div class="flex-shrink-0 text-center min-w-[60px]">
                                <div class="text-xs text-zinc-500">{{ $item['day'] }}</div>
                                <div class="text-sm font-bold">{{ $item['date'] }}</div>
                                <div class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($item['time'])->format('g:i A') }}</div>
                            </div>

                            <div class="flex-1">
                                <h4 class="font-semibold text-sm">{{ $item['title'] }}</h4>
                                <flux:badge color="blue" size="sm">{{ $item['category'] }}</flux:badge>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex gap-3">
                    <flux:button type="button" wire:click="import" variant="primary">
                        {{ __('Confirm Import') }}
                    </flux:button>
                    <flux:button type="button" wire:click="resetForm" variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            @endif
        </flux:card>
        @endif
    </div>
</flux:main>
