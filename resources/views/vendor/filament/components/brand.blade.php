@if (filled($brand = config('filament.brand')))
    <div @class([
        'filament-brand text-xl font-bold tracking-tight',
        'dark:text-white' => config('filament.dark_mode'),
    ])>
        <img src="{{ asset('/img/logo_sscom.png')}}" alt="SSCOM app" class="h-9 self-center"/>
    </div>
@endif
