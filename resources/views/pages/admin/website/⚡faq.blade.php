<?php

use App\Concerns\PublicWebsiteValidationRules;
use App\Models\FaqItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new
#[Layout('layouts.app.sidebar')]
#[Title('Manage FAQ Items')]
class extends Component {
    use PublicWebsiteValidationRules;

    public bool $showModal = false;
    public ?int $editingId = null;

    #[Url]
    public string $categoryFilter = '';

    public string $question = '';
    public string $answer = '';
    public string $category = 'general';
    public int $sort_order = 0;

    #[Computed]
    public function faqItems()
    {
        return FaqItem::query()
            ->when($this->categoryFilter, fn($q) => $q->where('category', $this->categoryFilter))
            ->ordered()
            ->get();
    }

    #[Computed]
    public function categories(): array
    {
        return [
            'general' => 'General',
            'admissions' => 'Admissions',
            'care' => 'Care Services',
            'visiting' => 'Visiting',
            'costs' => 'Costs & Fees',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $item = FaqItem::findOrFail($id);

        $this->editingId = $item->id;
        $this->question = $item->question;
        $this->answer = $item->answer;
        $this->category = $item->category;
        $this->sort_order = $item->sort_order;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate($this->faqItemRules());

        $data = [
            'question' => $this->question,
            'answer' => $this->answer,
            'category' => $this->category,
            'sort_order' => $this->sort_order,
        ];

        if ($this->editingId) {
            FaqItem::find($this->editingId)->update($data);
        } else {
            FaqItem::create($data);
        }

        $this->resetForm();
        $this->showModal = false;
        unset($this->faqItems);
    }

    public function delete(int $id): void
    {
        FaqItem::findOrFail($id)->delete();
        unset($this->faqItems);
    }

    public function toggleActive(int $id): void
    {
        $item = FaqItem::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        unset($this->faqItems);
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->question = '';
        $this->answer = '';
        $this->category = 'general';
        $this->sort_order = 0;
        $this->resetValidation();
    }
};

?>

<flux:main>
    <x-pages.admin.website-layout
        :heading="__('FAQ Items')"
        :subheading="__('Manage frequently asked questions displayed on the FAQ page')">

        <div class="space-y-6">
            <div class="flex flex-wrap justify-between gap-4">
                <flux:select wire:model.live="categoryFilter" class="w-48">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($this->categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>

                <flux:button variant="primary" wire:click="create" icon="plus">
                    {{ __('Add FAQ Item') }}
                </flux:button>
            </div>

            <flux:card>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Question') }}</flux:table.column>
                        <flux:table.column>{{ __('Category') }}</flux:table.column>
                        <flux:table.column>{{ __('Order') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse($this->faqItems as $item)
                            <flux:table.row :key="$item->id">
                                <flux:table.cell class="max-w-md">
                                    <div class="font-medium">{{ Str::limit($item->question, 60) }}</div>
                                    <div class="text-sm text-zinc-500 truncate">{{ Str::limit($item->answer, 80) }}</div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="zinc">{{ $this->categories[$item->category] ?? $item->category }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $item->sort_order }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge :color="$item->is_active ? 'green' : 'zinc'">
                                        {{ $item->is_active ? 'Active' : 'Inactive' }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-2">
                                        <flux:button variant="ghost" size="xs" wire:click="edit({{ $item->id }})" icon="pencil" />
                                        <flux:button variant="ghost" size="xs" wire:click="toggleActive({{ $item->id }})" icon="{{ $item->is_active ? 'eye-slash' : 'eye' }}" />
                                        <flux:button variant="ghost" size="xs" wire:click="delete({{ $item->id }})" wire:confirm="Are you sure you want to delete this FAQ item?" icon="trash" class="text-red-600 hover:text-red-700" />
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center py-8 text-zinc-500">
                                    {{ __('No FAQ items yet. Add your first one!') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>

        {{-- Modal --}}
        <flux:modal wire:model="showModal" class="max-w-lg">
            <div class="space-y-6">
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit FAQ Item') : __('Add FAQ Item') }}
                </flux:heading>

                <form wire:submit="save" class="space-y-4">
                    <flux:input
                        wire:model="question"
                        :label="__('Question')"
                        placeholder="What are your visiting hours?"
                        required
                    />

                    <flux:textarea
                        wire:model="answer"
                        :label="__('Answer')"
                        rows="4"
                        placeholder="Provide a clear and helpful answer..."
                        required
                    />

                    <div class="grid grid-cols-2 gap-4">
                        <flux:select wire:model="category" :label="__('Category')">
                            @foreach($this->categories as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input
                            wire:model.number="sort_order"
                            :label="__('Sort Order')"
                            type="number"
                            min="0"
                        />
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <flux:button variant="ghost" wire:click="$set('showModal', false)" type="button">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button variant="primary" type="submit">
                            {{ $editingId ? __('Update') : __('Create') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </flux:modal>
    </x-pages.admin.website-layout>
</flux:main>
