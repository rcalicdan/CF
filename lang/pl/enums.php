<?php

return [
    'userroles' => [
        'admin'    => 'Administrator',
        'employee' => 'Pracownik',
        'driver'   => 'Kierowca',
    ],
    'ordercarpetstatus' => [
        'pending'        => 'Oczekujące',
        'picked up'      => 'Odebrane',
        'at laundry'     => 'W pralni',
        'measured'       => 'Zmierzony',
        'completed'      => 'Zakończony',
        'waiting'        => 'Oczekiwanie',
        'delivered'      => 'Dostarczony',
        'not delivered'  => 'Nie dostarczony',
        'returned'       => 'Zwrócony',
        'complaint'      => 'Reklamacja',
        'under review'   => 'W trakcie weryfikacji',
    ],
    'orderstatus' => [
        'pending'    => 'Oczekujące',
        'accepted'   => 'Zaakceptowane',
        'processing' => 'W realizacji',
        'completed'  => 'Zakończone',
        'undelivered' => 'Niedostarczone',
        'cancelled'  => 'Anulowane',
    ],
    'orderdeliveryconfirmationtype' => [
        'data'       => 'Dane',
        'signature'  => 'Podpis',
    ],
    'costtype' => [
        'energy' => 'Energy',
        'water' => 'Water',
        'fuel' => 'Fuel',
        'wages' => 'Wages',
        'chemicals' => 'Chemicals',
        'supplies' => 'Supplies',
        'other' => 'Other',
    ],
];
