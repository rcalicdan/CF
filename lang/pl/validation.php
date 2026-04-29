<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

  'accepted' => 'Pole :attribute musi zostać zaakceptowane.',
  'accepted_if' => 'Pole :attribute musi zostać zaakceptowane gdy :other ma wartość :value.',
  'active_url' => 'Pole :attribute musi być prawidłowym adresem URL.',
  'after' => 'Pole :attribute musi być datą późniejszą niż :date.',
  'after_or_equal' => 'Pole :attribute musi być datą równą lub późniejszą niż :date.',
  'alpha' => 'Pole :attribute może zawierać tylko litery.',
  'alpha_dash' => 'Pole :attribute może zawierać tylko litery, cyfry, myślniki i podkreślenia.',
  'alpha_num' => 'Pole :attribute może zawierać tylko litery i cyfry.',
  'any_of' => 'Pole :attribute jest nieprawidłowe.',
  'array' => 'Pole :attribute musi być tablicą.',
  'ascii' => 'Pole :attribute może zawierać tylko pojedyncze znaki alfanumeryczne i symbole.',
  'before' => 'Pole :attribute musi być datą wcześniejszą niż :date.',
  'before_or_equal' => 'Pole :attribute musi być datą równą lub wcześniejszą niż :date.',
  'between' => [
    'array' => 'Pole :attribute musi zawierać od :min do :max elementów.',
    'file' => 'Plik :attribute musi mieć od :min do :max kilobajtów.',
    'numeric' => 'Pole :attribute musi mieć wartość od :min do :max.',
    'string' => 'Pole :attribute musi zawierać od :min do :max znaków.',
  ],
  'boolean' => 'Pole :attribute musi mieć wartość tak lub nie.',
  'can' => 'Pole :attribute zawiera niedozwoloną wartość.',
  'confirmed' => 'Potwierdzenie pola :attribute nie zgadza się.',
  'contains' => 'W polu :attribute brakuje wymaganej wartości.',
  'current_password' => 'Hasło jest nieprawidłowe.',
  'date' => 'Pole :attribute musi być prawidłową datą.',
  'date_equals' => 'Pole :attribute musi być datą równą :date.',
  'date_format' => 'Pole :attribute musi być w formacie :format.',
  'decimal' => 'Pole :attribute musi mieć :decimal miejsc po przecinku.',
  'declined' => 'Pole :attribute musi zostać odrzucone.',
  'declined_if' => 'Pole :attribute musi zostać odrzucone gdy :other ma wartość :value.',
  'different' => 'Pole :attribute i :other muszą się różnić.',
  'digits' => 'Pole :attribute musi składać się z :digits cyfr.',
  'digits_between' => 'Pole :attribute musi mieć od :min do :max cyfr.',
  'dimensions' => 'Pole :attribute ma nieprawidłowe wymiary obrazu.',
  'distinct' => 'Pole :attribute zawiera zduplikowaną wartość.',
  'doesnt_end_with' => 'Pole :attribute nie może kończyć się jednym z: :values.',
  'doesnt_start_with' => 'Pole :attribute nie może zaczynać się od jednego z: :values.',
  'email' => 'Pole :attribute musi być prawidłowym adresem e-mail.',
  'ends_with' => 'Pole :attribute musi kończyć się jednym z: :values.',
  'enum' => 'Wybrana wartość dla :attribute jest nieprawidłowa.',
  'exists' => 'Wybrana wartość dla :attribute jest nieprawidłowa.',
  'extensions' => 'Pole :attribute musi mieć jedno z rozszerzeń: :values.',
  'file' => 'Pole :attribute musi być plikiem.',
  'filled' => 'Pole :attribute musi mieć wartość.',
  'gt' => [
    'array' => 'Pole :attribute musi zawierać więcej niż :value elementów.',
    'file' => 'Plik :attribute musi być większy niż :value kilobajtów.',
    'numeric' => 'Pole :attribute musi być większe niż :value.',
    'string' => 'Pole :attribute musi zawierać więcej niż :value znaków.',
  ],
  'gte' => [
    'array' => 'Pole :attribute musi zawierać :value lub więcej elementów.',
    'file' => 'Plik :attribute musi mieć co najmniej :value kilobajtów.',
    'numeric' => 'Pole :attribute musi być równe lub większe niż :value.',
    'string' => 'Pole :attribute musi zawierać co najmniej :value znaków.',
  ],
  'hex_color' => 'Pole :attribute musi być prawidłowym kolorem w formacie szesnastkowym.',
  'image' => 'Pole :attribute musi być obrazem.',
  'in' => 'Wybrana wartość dla :attribute jest nieprawidłowa.',
  'in_array' => 'Pole :attribute musi istnieć w :other.',
  'in_array_keys' => 'Pole :attribute musi zawierać przynajmniej jeden z kluczy: :values.',
  'integer' => 'Pole :attribute musi być liczbą całkowitą.',
  'ip' => 'Pole :attribute musi być prawidłowym adresem IP.',
  'ipv4' => 'Pole :attribute musi być prawidłowym adresem IPv4.',
  'ipv6' => 'Pole :attribute musi być prawidłowym adresem IPv6.',
  'json' => 'Pole :attribute musi być prawidłowym ciągiem JSON.',
  'list' => 'Pole :attribute musi być listą.',
  'lowercase' => 'Pole :attribute musi być napisane małymi literami.',
  'lt' => [
    'array' => 'Pole :attribute musi zawierać mniej niż :value elementów.',
    'file' => 'Plik :attribute musi być mniejszy niż :value kilobajtów.',
    'numeric' => 'Pole :attribute musi być mniejsze niż :value.',
    'string' => 'Pole :attribute musi zawierać mniej niż :value znaków.',
  ],
  'lte' => [
    'array' => 'Pole :attribute nie może zawierać więcej niż :value elementów.',
    'file' => 'Plik :attribute nie może być większy niż :value kilobajtów.',
    'numeric' => 'Pole :attribute musi być równe lub mniejsze niż :value.',
    'string' => 'Pole :attribute nie może zawierać więcej niż :value znaków.',
  ],
  'mac_address' => 'Pole :attribute musi być prawidłowym adresem MAC.',
  'max' => [
    'array' => 'Pole :attribute nie może zawierać więcej niż :max elementów.',
    'file' => 'Plik :attribute nie może być większy niż :max kilobajtów.',
    'numeric' => 'Pole :attribute nie może być większe niż :max.',
    'string' => 'Pole :attribute nie może zawierać więcej niż :max znaków.',
  ],
  'max_digits' => 'Pole :attribute nie może mieć więcej niż :max cyfr.',
  'mimes' => 'Pole :attribute musi być plikiem typu: :values.',
  'mimetypes' => 'Pole :attribute musi być plikiem typu: :values.',
  'min' => [
    'array' => 'Pole :attribute musi zawierać co najmniej :min elementów.',
    'file' => 'Plik :attribute musi mieć co najmniej :min kilobajtów.',
    'numeric' => 'Pole :attribute musi być co najmniej :min.',
    'string' => 'Pole :attribute musi zawierać co najmniej :min znaków.',
  ],
  'min_digits' => 'Pole :attribute musi mieć co najmniej :min cyfr.',
  'missing' => 'Pole :attribute musi być puste.',
  'missing_if' => 'Pole :attribute musi być puste gdy :other ma wartość :value.',
  'missing_unless' => 'Pole :attribute musi być puste chyba że :other ma wartość :value.',
  'missing_with' => 'Pole :attribute musi być puste gdy :values jest obecne.',
  'missing_with_all' => 'Pole :attribute musi być puste gdy :values są obecne.',
  'multiple_of' => 'Pole :attribute musi być wielokrotnością :value.',
  'not_in' => 'Wybrana wartość dla :attribute jest nieprawidłowa.',
  'not_regex' => 'Format pola :attribute jest nieprawidłowy.',
  'numeric' => 'Pole :attribute musi być liczbą.',
  'password' => [
    'letters' => 'Pole :attribute musi zawierać co najmniej jedną literę.',
    'mixed' => 'Pole :attribute musi zawierać co najmniej jedną wielką i jedną małą literę.',
    'numbers' => 'Pole :attribute musi zawierać co najmniej jedną cyfrę.',
    'symbols' => 'Pole :attribute musi zawierać co najmniej jeden znak specjalny.',
    'uncompromised' => 'Podane :attribute pojawiło się w wycieku danych. Proszę wybrać inne :attribute.',
  ],
  'present' => 'Pole :attribute musi być obecne.',
  'present_if' => 'Pole :attribute musi być obecne gdy :other ma wartość :value.',
  'present_unless' => 'Pole :attribute musi być obecne chyba że :other ma wartość :value.',
  'present_with' => 'Pole :attribute musi być obecne gdy :values jest obecne.',
  'present_with_all' => 'Pole :attribute musi być obecne gdy :values są obecne.',
  'prohibited' => 'Pole :attribute jest zabronione.',
  'prohibited_if' => 'Pole :attribute jest zabronione gdy :other ma wartość :value.',
  'prohibited_if_accepted' => 'Pole :attribute jest zabronione gdy :other zostało zaakceptowane.',
  'prohibited_if_declined' => 'Pole :attribute jest zabronione gdy :other zostało odrzucone.',
  'prohibited_unless' => 'Pole :attribute jest zabronione chyba że :other jest w :values.',
  'prohibits' => 'Pole :attribute zabrania obecności :other.',
  'regex' => 'Format pola :attribute jest nieprawidłowy.',
  'required' => 'Pole :attribute jest wymagane.',
  'required_array_keys' => 'Pole :attribute musi zawierać wpisy dla: :values.',
  'required_if' => 'Pole :attribute jest wymagane gdy :other ma wartość :value.',
  'required_if_accepted' => 'Pole :attribute jest wymagane gdy :other zostało zaakceptowane.',
  'required_if_declined' => 'Pole :attribute jest wymagane gdy :other zostało odrzucone.',
  'required_unless' => 'Pole :attribute jest wymagane chyba że :other jest w :values.',
  'required_with' => 'Pole :attribute jest wymagane gdy :values jest obecne.',
  'required_with_all' => 'Pole :attribute jest wymagane gdy :values są obecne.',
  'required_without' => 'Pole :attribute jest wymagane gdy :values nie jest obecne.',
  'required_without_all' => 'Pole :attribute jest wymagane gdy żadne z :values nie są obecne.',
  'same' => 'Pole :attribute musi być takie samo jak :other.',
  'size' => [
    'array' => 'Pole :attribute musi zawierać :size elementów.',
    'file' => 'Plik :attribute musi mieć :size kilobajtów.',
    'numeric' => 'Pole :attribute musi mieć wartość :size.',
    'string' => 'Pole :attribute musi zawierać :size znaków.',
  ],
  'starts_with' => 'Pole :attribute musi zaczynać się od jednego z: :values.',
  'string' => 'Pole :attribute musi być tekstem.',
  'timezone' => 'Pole :attribute musi być prawidłową strefą czasową.',
  'unique' => 'Podane :attribute jest już zajęte.',
  'uploaded' => 'Nie udało się przesłać :attribute.',
  'uppercase' => 'Pole :attribute musi być napisane wielkimi literami.',
  'url' => 'Pole :attribute musi być prawidłowym adresem URL.',
  'ulid' => 'Pole :attribute musi być prawidłowym identyfikatorem ULID.',
  'uuid' => 'Pole :attribute musi być prawidłowym identyfikatorem UUID.',

  /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

  'custom' => [
    'attribute-name' => [
      'rule-name' => 'custom-message',
    ],
  ],

  /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

  'attributes' => [
    'image_file' => [
      'not_uploaded_file' => 'Plik musi być prawidłowo przesłanym plikiem.',
      'invalid_extension' => 'Dozwolone są tylko pliki w formatach: :extensions.',
      'invalid_mime_type' => 'Wykryto nieprawidłowy typ pliku obrazu.',
      'not_valid_image' => 'Plik nie jest prawidłowym obrazem.',
      'file_too_large' => 'Rozmiar obrazu nie może przekraczać :max_size.',
    ],

    'carpet_photos' => [
      'required' => 'Wybierz co najmniej jedno zdjęcie do przesłania.',
      'invalid_format' => 'Podano nieprawidłowy format zdjęcia.',
      'min_photos' => 'Wybierz co najmniej jedno zdjęcie.',
      'max_photos' => 'Możesz przesłać maksymalnie 10 zdjęć jednocześnie.',
      'photo_required' => 'Każdy plik zdjęcia jest wymagany.',
    ],
  ],

];
