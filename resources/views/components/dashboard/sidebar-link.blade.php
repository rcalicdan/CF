@props([
    'href' => '#',
    'icon' => '',
    'active' => false,
    'label' => '',
    'isCollapsed' => false,
])

<a wire:navigate href="{{ $href }}"
    :class="{ 'justify-center': {{ $isCollapsed ? 'true' : 'isDesktopSidebarCollapsed' }} }"
    class="sidebar-link flex items-center px-6 py-3 {{ $active ? 'active' : 'text-gray-500' }}">
    <i class="{{ $active ? 'sidebar-icon ' : '' }}{{ $icon }} text-xl flex-shrink-0"></i>
    <span class="{{ $active ? 'sidebar-text mx-3 whitespace-nowrap font-medium' : 'mx-3 whitespace-nowrap' }}"
        x-show="!{{ $isCollapsed ? 'true' : 'isDesktopSidebarCollapsed' }}" x-transition>{{ $label }}</span>
</a>
