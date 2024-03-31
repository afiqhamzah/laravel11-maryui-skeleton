<?php

use App\Models\User;
use Mary\Traits\Toast;
use App\Models\Country;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use App\Models\Language;

new class extends Component {
    use Toast, WithFileUploads;

    public User $user;

    #[Rule('required')]
    public array $my_languages = [];

    // You could use Livewire "form object" instead.
    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    // Optional
    #[Rule('sometimes')]
    public ?int $country_id = null;

    #[RULE('nullable|image|max:1024')]
    public $photo;

    public function save(): void
    {
        // Validate
        $data = $this->validate();

        // Update
        $this->user->update($data);

        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $this->user->update(['avatar' => "/storage/$url"]);
        }

        // Sync selection
        $this->user->languages()->sync($this->my_languages);

        // You can toast and redirect to any route
        $this->success('User updated with success.', redirectTo: '/users');
    }

    // We also need this to fill Countries combobox on upcoming form
    public function with(): array
    {
        return [
            'countries' => Country::all(),
            'languages' => Language::all(),
        ];
    }

    public function mount(): void
    {
        $this->fill($this->user);

        // Fill the selected languages property
        $this->my_languages = $this->user->languages->pluck('id')->all();
    }
}; ?>

<div>
    <x-header title="Update {{ $user->name }}" separator />

    <div class="grid gap-5 lg:grid-cols-2">
        <x-form wire:submit="save">
            <x-file label="Avatar" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                <img src="{{ $user->avatar ?? '/empty-user.jpg' }}" class="h-40 rounded-lg" />
            </x-file>
            <x-input label="Name" wire:model="name" />
            <x-input label="Email" wire:model="email" />
            <x-select label="Country" wire:model="country_id" :options="$countries" placeholder="---" />

            {{-- Multi selection --}}
            <x-choices-offline
            label="My languages"
            wire:model="my_languages"
            :options="$languages"
            searchable />

            <x-slot:actions>
                <x-button label="Cancel" link="/users" />
                {{-- The important thing here is `type="submit"` --}}
                {{-- The spinner property is nice! --}}
                <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
            </x-slot:actions>
        </x-form>
        <div>
            <img src={{ url('/Hamburger-pana.png') }}>
        </div>
    </div>


</div>
