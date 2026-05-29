<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AcufaraInfoWidget extends Widget
{
    protected static ?int $sort = -2;
    protected int | string | array $columnSpan = 1;
    
    protected string $view = 'filament.widgets.acufara-info-widget';
}
