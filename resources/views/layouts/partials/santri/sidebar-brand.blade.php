<div class="px-6 py-5 border-b border-gray-100">
  <a href="{{ $brandRoute }}" wire:navigate class="flex items-center gap-2.5 group">
    @if($logoRel)
      <img class="h-8 w-8 object-contain" src="{{ asset($logoRel) }}" alt="PPM KH2">
    @else
      <i data-lucide="shield" class="w-8 h-8 text-emerald-600"></i>
    @endif
    <span class="text-base font-semibold text-gray-900 group-hover:text-emerald-700">PPM Khoirul Huda 2</span>
  </a>
</div>
