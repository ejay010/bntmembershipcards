<?php

use App\Models\Member;
use App\Mail\MembershipCardMail;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $tierFilter = '';

    public bool $showFormModal = false;
    public bool $showPreviewModal = false;

    public ?int $editingMemberId = null;
    public string $name = '';
    public string $email = '';
    public string $tier = 'Individual';
    public string $status = 'active';
    public ?string $expires_at = '';

    public ?int $previewMemberId = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'tier' => 'required|string|in:Individual,Family,Gold,Life',
            'status' => 'required|string|in:active,expired,inactive',
            'expires_at' => 'nullable|date',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTierFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->editingMemberId = null;
        $this->name = '';
        $this->email = '';
        $this->tier = 'Individual';
        $this->status = 'active';
        $this->expires_at = '';
        $this->showFormModal = true;
    }

    public function openEdit(int $memberId): void
    {
        $this->resetValidation();
        $member = Member::findOrFail($memberId);
        $this->editingMemberId = $member->id;
        $this->name = $member->name;
        $this->email = $member->email;
        $this->tier = $member->tier;
        $this->status = $member->status;
        $this->expires_at = $member->expires_at ? $member->expires_at->format('Y-m-d') : '';
        $this->showFormModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingMemberId) {
            $member = Member::findOrFail($this->editingMemberId);
            $member->update($validated);
            Flux::toast(variant: 'success', text: __('Member updated.'));
        } else {
            $member = Member::create(array_merge($validated, [
                'created_by' => auth()->id(),
            ]));
            Flux::toast(variant: 'success', text: __('Member enrolled successfully.'));
        }

        $this->showFormModal = false;
    }

    public function delete(int $memberId): void
    {
        $member = Member::findOrFail($memberId);
        $member->delete();
        Flux::toast(variant: 'success', text: __('Member removed.'));
    }

    public function sendCardEmail(int $memberId): void
    {
        $member = Member::findOrFail($memberId);
        
        Mail::to($member->email)->send(new MembershipCardMail($member));
        
        $member->update([
            'email_sent_at' => now(),
        ]);

        Flux::toast(variant: 'success', text: __('Membership card email sent.'));
    }

    public function openPreview(int $memberId): void
    {
        $this->previewMemberId = $memberId;
        $this->showPreviewModal = true;
    }

    #[Computed]
    public function previewMember(): ?Member
    {
        return $this->previewMemberId ? Member::find($this->previewMemberId) : null;
    }

    #[Computed]
    public function members()
    {
        return Member::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('membership_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->tierFilter, function ($query) {
                $query->where('tier', $this->tierFilter);
            })
            ->latest()
            ->paginate(10);
    }
};
?>

<div class="w-full p-6 space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-extrabold tracking-tight">{{ __('Members Directory') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Enroll, edit, and issue digital membership cards to BNT members.') }}</flux:text>
        </div>
        <div>
            <flux:button wire:click="openCreate" variant="primary" icon="plus" class="bg-emerald-600 hover:bg-emerald-700 border-none">
                {{ __('Enroll Member') }}
            </flux:button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-200 dark:border-zinc-800">
        <!-- Search -->
        <div class="md:col-span-2">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search by name, email, or member number...') }}" icon="magnifying-glass" clearable />
        </div>
        <!-- Status Filter -->
        <div>
            <flux:select wire:model.live="statusFilter" placeholder="{{ __('All Statuses') }}">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
            </flux:select>
        </div>
        <!-- Tier Filter -->
        <div>
            <flux:select wire:model.live="tierFilter" placeholder="{{ __('All Tiers') }}">
                <flux:select.option value="">{{ __('All Tiers') }}</flux:select.option>
                <flux:select.option value="Individual">{{ __('Individual') }}</flux:select.option>
                <flux:select.option value="Family">{{ __('Family') }}</flux:select.option>
                <flux:select.option value="Gold">{{ __('Gold') }}</flux:select.option>
                <flux:select.option value="Life">{{ __('Life') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    <!-- Table Section -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800 text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-950 text-zinc-500 dark:text-zinc-400 uppercase text-xs font-semibold tracking-wider">
                    <tr>
                        <th class="px-6 py-4">{{ __('Member ID') }}</th>
                        <th class="px-6 py-4">{{ __('Member Details') }}</th>
                        <th class="px-6 py-4">{{ __('Tier') }}</th>
                        <th class="px-6 py-4">{{ __('Status') }}</th>
                        <th class="px-6 py-4">{{ __('Card Sent') }}</th>
                        <th class="px-6 py-4">{{ __('Wallet Downloads') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse ($this->members as $member)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-mono font-bold text-zinc-900 dark:text-zinc-100">
                                {{ $member->membership_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $member->name }}</div>
                                <div class="text-xs text-zinc-500">{{ $member->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300">
                                    {{ $member->tier }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($member->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-400">
                                        {{ __('Active') }}
                                    </span>
                                @elseif ($member->status === 'expired')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-950/40 text-red-800 dark:text-red-400">
                                        {{ __('Expired') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-300">
                                        {{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-zinc-500">
                                @if ($member->email_sent_at)
                                    <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                                        <span>✓</span> {{ $member->email_sent_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-zinc-400">{{ __('Never') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <div class="flex flex-col gap-1">
                                    @if ($member->apple_wallet_downloaded_at)
                                        <span class="text-emerald-600 dark:text-emerald-400">🍏 Apple: {{ $member->apple_wallet_downloaded_at->format('M d, H:i') }}</span>
                                    @else
                                        <span class="text-zinc-400">🍏 Apple: {{ __('No') }}</span>
                                    @endif

                                    @if ($member->google_wallet_downloaded_at)
                                        <span class="text-emerald-600 dark:text-emerald-400">💳 Google: {{ $member->google_wallet_downloaded_at->format('M d, H:i') }}</span>
                                    @else
                                        <span class="text-zinc-400">💳 Google: {{ __('No') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <flux:dropdown>
                                    <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" />
                                    <flux:menu>
                                        <flux:menu.item wire:click="sendCardEmail({{ $member->id }})" icon="envelope">
                                            {{ __('Email Membership Card') }}
                                        </flux:menu.item>
                                        <flux:menu.item wire:click="openPreview({{ $member->id }})" icon="eye">
                                            {{ __('Preview Card') }}
                                        </flux:menu.item>
                                        <flux:menu.item wire:click="openEdit({{ $member->id }})" icon="pencil">
                                            {{ __('Edit Details') }}
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item wire:click="delete({{ $member->id }})" icon="trash" variant="danger">
                                            {{ __('Delete Member') }}
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                <div class="text-lg font-semibold">{{ __('No members found') }}</div>
                                <div class="text-sm mt-1">{{ __('Try adjusting your filters or enroll a new member.') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if ($this->members->hasPages())
            <div class="px-6 py-4 border-t border-zinc-200 dark:border-zinc-800">
                {{ $this->members->links() }}
            </div>
        @endif
    </div>

    <!-- Enrollment Form Modal -->
    <flux:modal wire:model="showFormModal" class="md:max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="font-extrabold tracking-tight">
                    {{ $editingMemberId ? __('Edit Member details') : __('Enroll New Member') }}
                </flux:heading>
                <flux:text class="mt-1">
                    {{ __('Provide membership registration details below.') }}
                </flux:text>
            </div>

            <form wire:submit.prevent="save" class="space-y-4">
                <!-- Name -->
                <flux:field>
                    <flux:label>{{ __('Full Name') }}</flux:label>
                    <flux:input wire:model="name" placeholder="{{ __('Jane Doe') }}" required />
                    <flux:error name="name" />
                </flux:field>

                <!-- Email -->
                <flux:field>
                    <flux:label>{{ __('Email Address') }}</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="{{ __('jane.doe@example.com') }}" required />
                    <flux:error name="email" />
                </flux:field>

                <!-- Tier -->
                <flux:field>
                    <flux:label>{{ __('Membership Tier') }}</flux:label>
                    <flux:select wire:model="tier">
                        <flux:select.option value="Individual">{{ __('Individual') }}</flux:select.option>
                        <flux:select.option value="Family">{{ __('Family') }}</flux:select.option>
                        <flux:select.option value="Gold">{{ __('Gold') }}</flux:select.option>
                        <flux:select.option value="Life">{{ __('Life') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="tier" />
                </flux:field>

                <!-- Status -->
                <flux:field>
                    <flux:label>{{ __('Status') }}</flux:label>
                    <flux:select wire:model="status">
                        <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                        <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
                        <flux:select.option value="inactive">{{ __('Inactive') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="status" />
                </flux:field>

                <!-- Expiration -->
                <flux:field>
                    <flux:label>{{ __('Expiration Date (Optional)') }}</flux:label>
                    <flux:input wire:model="expires_at" type="date" />
                    <flux:error name="expires_at" />
                </flux:field>

                <!-- Footer Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button wire:click="$set('showFormModal', false)" variant="ghost">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary" class="bg-emerald-600 hover:bg-emerald-700 border-none">{{ __('Save Member') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    <!-- Card Preview Modal -->
    <flux:modal wire:model="showPreviewModal" class="md:max-w-md">
        @if ($this->previewMember)
            <div class="space-y-6">
                <div class="text-center">
                    <flux:heading size="lg" class="font-extrabold tracking-tight">{{ __('Virtual Membership Card') }}</flux:heading>
                    <flux:text class="mt-1">{{ __('Mockup of the card in the member wallet.') }}</flux:text>
                </div>

                <!-- BNT Premium Pass Design -->
                <div class="bg-gradient-to-br from-emerald-800 to-emerald-950 rounded-2xl p-6 text-white shadow-2xl border border-emerald-700/30 relative overflow-hidden">
                    <div class="absolute -right-16 -top-16 w-36 h-36 bg-white/5 rounded-full blur-xl pointer-events-none"></div>
                    <div class="absolute -left-16 -bottom-16 w-36 h-36 bg-emerald-500/10 rounded-full blur-xl pointer-events-none"></div>
                    
                    <div class="flex justify-between items-start border-b border-white/10 pb-3 mb-6">
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-widest text-emerald-300">{{ __('Bahamas National Trust') }}</p>
                            <p class="text-sm font-semibold tracking-wide text-white/90">{{ __('MEMBER CARD') }}</p>
                        </div>
                        <span class="text-2xl font-bold">🇧🇸</span>
                    </div>

                    <div class="mb-6">
                        <p class="text-[9px] text-white/60 uppercase tracking-widest mb-0.5">{{ __('Member Name') }}</p>
                        <p class="text-xl font-bold tracking-wide leading-none">{{ $this->previewMember->name }}</p>
                    </div>

                    <div class="flex justify-between items-end">
                        <div class="space-y-3">
                            <div>
                                <p class="text-[9px] text-white/60 uppercase tracking-widest mb-0.5">{{ __('Member ID') }}</p>
                                <p class="text-xs font-mono font-bold">{{ $this->previewMember->membership_number }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-emerald-300 uppercase tracking-widest mb-0.5">{{ __('Tier') }}</p>
                                <p class="text-sm font-black text-amber-400 uppercase tracking-wider leading-none">{{ $this->previewMember->tier }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] text-white/60 uppercase tracking-widest mb-0.5">{{ __('Expires') }}</p>
                                <p class="text-xs font-semibold leading-none">{{ $this->previewMember->expires_at ? $this->previewMember->expires_at->format('M d, Y') : __('NEVER') }}</p>
                            </div>
                        </div>
                        <div>
                            <!-- Dynamic QR Code representation -->
                            <div class="bg-white p-2 rounded-lg inline-block shadow-md">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode(route('members.pass.apple', $this->previewMember->uuid)) }}" alt="QR Code" width="80" height="80" class="block border-0">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Simulation/Downloads links -->
                <div class="space-y-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:heading size="sm" class="font-bold text-center mb-2">{{ __('Download Digital Pass') }}</flux:heading>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <flux:button href="{{ route('members.pass.apple', $this->previewMember->uuid) }}" variant="filled" icon="arrow-down-tray" class="bg-black hover:bg-zinc-900 text-white border-none">
                            {{ __('Apple Wallet') }}
                        </flux:button>
                        
                        <flux:button href="{{ route('members.pass.google', $this->previewMember->uuid) }}" variant="filled" icon="arrow-down-tray" class="bg-zinc-800 hover:bg-zinc-700 text-white border-none">
                            {{ __('Google Wallet') }}
                        </flux:button>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex justify-end pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <flux:button wire:click="$set('showPreviewModal', false)" variant="ghost">{{ __('Close') }}</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>