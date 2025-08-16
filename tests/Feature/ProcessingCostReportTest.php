<?php

use App\ActionService\CsvReportService;
use App\ActionService\PdfReportService;
use App\Livewire\ProcessingCosts\Charts;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

$testData = [
    'weeklyData' => [
        [
            'label' => 'Pon',
            'full_name' => 'Poniedzialek',
            'value' => 1500.50,
            'count' => 5,
            'average' => 300.10
        ],
        [
            'label' => 'Wt',
            'full_name' => 'Wtorek',
            'value' => 2100.75,
            'count' => 7,
            'average' => 300.11
        ]
    ],
    'monthlyData' => [
        [
            'label' => 'Sty',
            'full_name' => 'Styczen',
            'value' => 15000.00,
            'count' => 50,
            'average' => 300.00,
            'min' => 50.00,
            'max' => 1200.00
        ],
        [
            'label' => 'Lut',
            'full_name' => 'Luty',
            'value' => 18000.00,
            'count' => 60,
            'average' => 300.00,
            'min' => 75.00,
            'max' => 1500.00
        ]
    ],
    'yearlyData' => [
        [
            'label' => '2023',
            'full_name' => '2023',
            'value' => 180000.00,
            'count' => 600,
            'average' => 300.00,
            'quarters' => [
                'q1' => 45000.00,
                'q2' => 45000.00,
                'q3' => 45000.00,
                'q4' => 45000.00
            ]
        ]
    ],
    'costTypeData' => [
        [
            'type' => 'material',
            'label' => 'Materiały',
            'value' => 90000.00,
            'count' => 300,
            'average' => 300.00,
            'percentage' => 50.0
        ],
        [
            'type' => 'labor',
            'label' => 'Praca',
            'value' => 90000.00,
            'count' => 300,
            'average' => 300.00,
            'percentage' => 50.0
        ]
    ],
    'monthlyComparison' => [
        'current_month' => 18000.00,
        'previous_month' => 15000.00,
        'current_count' => 60,
        'previous_count' => 50,
        'percentage_change' => 20.0
    ]
];

it('can generate pdf report', function () use ($testData) {
    Storage::fake('public');

    $service = new PdfReportService();
    $filename = $service->generateProcessingCostsReport($testData);

    expect($filename)->toBeString()
        ->and($filename)->toMatch('/raport_kosztow_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}\.pdf$/');

    expect(Storage::disk('public')->exists($filename))->toBeTrue();

    $content = Storage::disk('public')->get($filename);
    expect($content)->not->toBeEmpty();
});

it('can generate csv report', function () use ($testData) {
    Storage::fake('public');

    $service = new CsvReportService();
    $filename = $service->generateProcessingCostsReport($testData);

    expect($filename)->toBeString()
        ->and($filename)->toMatch('/raport_kosztow_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.csv$/');

    expect(Storage::disk('public')->exists($filename))->toBeTrue();

    $content = Storage::disk('public')->get($filename);
    expect($content)->not->toBeEmpty()
        ->and($content)->toContain('Raport kosztów przetwarzania')
        ->and($content)->toContain('PODSUMOWANIE')
        ->and($content)->toContain('KOSZTY TYGODNIOWE')
        ->and($content)->toContain('KOSZTY MIESIĘCZNE')
        ->and($content)->toContain('KOSZTY ROCZNE')
        ->and($content)->toContain('KOSZTY WG TYPÓW');
});

it('can generate pdf from livewire component', function () {
    Storage::fake('public');

    Livewire::test(Charts::class)
        ->call('generatePdf')
        ->assertDispatched('download-pdf');
});

it('can generate csv from livewire component', function () {
    Storage::fake('public');

    Livewire::test(Charts::class)
        ->call('generateCsv')
        ->assertDispatched('download-csv');
});

it('has pdf report service class', function () {
    expect(class_exists(PdfReportService::class))->toBeTrue();
});

it('has csv report service class', function () {
    expect(class_exists(CsvReportService::class))->toBeTrue();
});

it('can generate reports with actual component data', function () use ($testData) {
    Storage::fake('public');
    
    $componentInstance = new Charts();
    $componentInstance->mount(); 

    $data = [
        'weeklyData' => $componentInstance->weeklyData ?: $testData['weeklyData'],
        'monthlyData' => $componentInstance->monthlyData ?: $testData['monthlyData'], 
        'yearlyData' => $componentInstance->yearlyData ?: $testData['yearlyData'],
        'costTypeData' => $componentInstance->costTypeData ?: $testData['costTypeData'],
        'monthlyComparison' => $testData['monthlyComparison'] 
    ];
    
    $pdfService = new PdfReportService();
    $pdfFilename = $pdfService->generateProcessingCostsReport($data);
    
    expect($pdfFilename)->toBeString();
    expect(Storage::disk('public')->exists($pdfFilename))->toBeTrue();
    
    $csvService = new CsvReportService();
    $csvFilename = $csvService->generateProcessingCostsReport($data);
    
    expect($csvFilename)->toBeString();
    expect(Storage::disk('public')->exists($csvFilename))->toBeTrue();
    
    $csvContent = Storage::disk('public')->get($csvFilename);
    expect($csvContent)->toContain('Raport kosztów przetwarzania');
});