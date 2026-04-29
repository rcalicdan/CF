<?php

return [
    'userroles' => [
        'admin'    => 'Administrator',
        'employee' => 'Pracownik',
        'driver'   => 'Kierowca',
    ],
    'ordercarpetstatus' => [
        'pending'        => 'Oczekujące',
        'picked up'      => 'Odebrane / Zaakceptowane',
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
        'processing' => 'Przyjęte / W realizacji',
        'completed'  => 'Zakończone / Zrealizowane',
        'undelivered' => 'Niedostarczone',
        'delivered'  => 'Dostarczone',
        'cancelled'  => 'Anulowane',
    ],
    'orderdeliveryconfirmationtype' => [
        'data'       => 'Dane',
        'signature'  => 'Podpis',
    ],
    'costtype' => [
        'energy' => 'Energia',
        'water' => 'Woda',
        'fuel' => 'Paliwo',
        'wages' => 'Wynagrodzenia',
        'chemicals' => 'Chemikalia',
        'supplies' => 'Materiały',
        'other' => 'Inne',
    ],
    'complaintstatus' => [
        'open' => 'Otwarte',
        'in progress' => 'W trakcie realizacji',
        'resolved' => 'Zakończone',
        'rejected' => 'Odrzucone',
        'closed' => 'Zamknięte',
    ],
];
