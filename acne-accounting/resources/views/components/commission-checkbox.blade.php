@props([
    'show' => false,         // Alpine variable controlling visibility (e.g., showCommissionCheckbox)
    'ratePercent' => 0,   // Alpine variable holding the commission rate percentage (e.g., commissionRatePercent)
    'checked' => false,   // Alpine variable bound via x-model (e.g., addCommissionChecked / applyCommission)
    'id' => 'add_commission', // ID for the checkbox and label
    'name' => 'add_commission',// Name attribute for the checkbox
])

{{-- This div controls the visibility based on the 'show' prop --}}
<div x-show="{{ $show }}" class="mt-4 p-3 border border-blue-300 dark:border-blue-700 rounded bg-blue-50 dark:bg-gray-700">
     <label for="{{ $id }}" class="flex items-center">
         {{-- Bind the checkbox's checked state to the 'checked' prop using x-model --}}
        <x-checkbox
            :id="$id"
            :name="$name"
            value="1"
            x-model="{{ $checked }}"
            {{ $attributes }} {{-- Allow passing additional attributes like @change --}}
        />
        {{-- Display the commission rate dynamically --}}
        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">Add Agency Commission (<span x-text="{{ $ratePercent }}"></span>%)</span>
    </label>
    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">If checked, the commission will be added to the transfer amount.</p>
</div> 