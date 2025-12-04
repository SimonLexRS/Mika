@props(['class' => 'w-8 h-8'])

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Fondo circular -->
    <circle cx="20" cy="20" r="20" fill="#5D3FD3"/>

    <!-- Letra M estilizada -->
    <path d="M10 28V14L15 22L20 14L25 22L30 14V28" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>

    <!-- Punto decorativo -->
    <circle cx="20" cy="10" r="2" fill="white"/>
</svg>
