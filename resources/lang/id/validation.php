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

    'accepted' => 'Field :attribute harus diterima.',
    'active_url' => 'Field :attribute bukan URL yang valid.',
    'after' => 'Field :attribute harus tanggal setelah :date.',
    'after_or_equal' => 'Field :attribute harus tanggal setelah atau sama dengan :date.',
    'alpha' => 'Field :attribute hanya boleh berisi huruf.',
    'alpha_dash' => 'Field :attribute hanya boleh berisi huruf, angka, dan strip.',
    'alpha_num' => 'Field :attribute hanya boleh berisi huruf dan angka.',
    'array' => 'Field :attribute harus berupa array.',
    'before' => 'Field :attribute harus tanggal sebelum :date.',
    'before_or_equal' => 'Field :attribute harus tanggal sebelum atau sama dengan :date.',
    'between' => [
        'numeric' => 'Field :attribute harus antara :min dan :max.',
        'file' => 'Field :attribute harus antara :min dan :max kilobytes.',
        'string' => 'Field :attribute harus antara :min dan :max karakter.',
        'array' => 'Field :attribute harus memiliki antara :min dan :max item.',
    ],
    'boolean' => 'Field :attribute harus true atau false.',
    'confirmed' => 'Konfirmasi field :attribute tidak cocok.',
    'date' => 'Field :attribute bukan tanggal yang valid.',
    'date_equals' => 'Field :attribute harus tanggal yang sama dengan :date.',
    'date_format' => 'Field :attribute tidak cocok dengan format :format.',
    'different' => 'Field :attribute dan :other harus berbeda.',
    'digits' => 'Field :attribute harus :digits digit.',
    'digits_between' => 'Field :attribute harus antara :min dan :max digit.',
    'dimensions' => 'Field :attribute memiliki dimensi gambar yang tidak valid.',
    'distinct' => 'Field :attribute memiliki nilai duplikat.',
    'email' => 'Field :attribute harus alamat email yang valid.',
    'ends_with' => 'Field :attribute harus diakhiri dengan salah satu dari: :values.',
    'exists' => 'Field :attribute yang dipilih tidak valid.',
    'file' => 'Field :attribute harus berupa file.',
    'filled' => 'Field :attribute harus memiliki nilai.',
    'gt' => [
        'numeric' => 'Field :attribute harus lebih besar dari :value.',
        'file' => 'Field :attribute harus lebih besar dari :value kilobytes.',
        'string' => 'Field :attribute harus lebih besar dari :value karakter.',
        'array' => 'Field :attribute harus memiliki lebih dari :value item.',
    ],
    'gte' => [
        'numeric' => 'Field :attribute harus lebih besar dari atau sama dengan :value.',
        'file' => 'Field :attribute harus lebih besar dari atau sama dengan :value kilobytes.',
        'string' => 'Field :attribute harus lebih besar dari atau sama dengan :value karakter.',
        'array' => 'Field :attribute harus memiliki :value item atau lebih.',
    ],
    'image' => 'Field :attribute harus berupa gambar.',
    'in' => 'Field :attribute yang dipilih tidak valid.',
    'in_array' => 'Field :attribute tidak ada di :other.',
    'integer' => 'Field :attribute harus berupa integer.',
    'ip' => 'Field :attribute harus alamat IP yang valid.',
    'ipv4' => 'Field :attribute harus alamat IPv4 yang valid.',
    'ipv6' => 'Field :attribute harus alamat IPv6 yang valid.',
    'json' => 'Field :attribute harus string JSON yang valid.',
    'lt' => [
        'numeric' => 'Field :attribute harus kurang dari :value.',
        'file' => 'Field :attribute harus kurang dari :value kilobytes.',
        'string' => 'Field :attribute harus kurang dari :value karakter.',
        'array' => 'Field :attribute harus memiliki kurang dari :value item.',
    ],
    'lte' => [
        'numeric' => 'Field :attribute harus kurang dari atau sama dengan :value.',
        'file' => 'Field :attribute harus kurang dari atau sama dengan :value kilobytes.',
        'string' => 'Field :attribute harus kurang dari atau sama dengan :value karakter.',
        'array' => 'Field :attribute tidak boleh memiliki lebih dari :value item.',
    ],
    'max' => [
        'numeric' => 'Field :attribute tidak boleh lebih besar dari :max.',
        'file' => 'Field :attribute tidak boleh lebih besar dari :max kilobytes.',
        'string' => 'Field :attribute tidak boleh lebih besar dari :max karakter.',
        'array' => 'Field :attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'mimes' => 'Field :attribute harus berupa file dengan tipe: :values.',
    'mimetypes' => 'Field :attribute harus berupa file dengan tipe: :values.',
    'min' => [
        'numeric' => 'Field :attribute minimal :min.',
        'file' => 'Field :attribute minimal :min kilobytes.',
        'string' => 'Field :attribute minimal :min karakter.',
        'array' => 'Field :attribute minimal memiliki :min item.',
    ],
    'multiple_of' => 'Field :attribute harus kelipatan dari :value.',
    'not_in' => 'Field :attribute yang dipilih tidak valid.',
    'not_regex' => 'Format field :attribute tidak valid.',
    'numeric' => 'Field :attribute harus berupa angka.',
    'password' => 'Password salah.',
    'present' => 'Field :attribute harus ada.',
    'regex' => 'Format field :attribute tidak valid.',
    'required' => 'Field :attribute wajib diisi.',
    'required_if' => 'Field :attribute wajib diisi bila :other adalah :value.',
    'required_unless' => 'Field :attribute wajib diisi kecuali :other ada di :values.',
    'required_with' => 'Field :attribute wajib diisi bila terdapat :values.',
    'required_with_all' => 'Field :attribute wajib diisi bila terdapat :values.',
    'required_without' => 'Field :attribute wajib diisi bila tidak terdapat :values.',
    'required_without_all' => 'Field :attribute wajib diisi bila tidak terdapat ada :values.',
    'same' => 'Field :attribute dan :other harus sama.',
    'size' => [
        'numeric' => 'Field :attribute harus berukuran :size.',
        'file' => 'Field :attribute harus berukuran :size kilobytes.',
        'string' => 'Field :attribute harus berukuran :size karakter.',
        'array' => 'Field :attribute harus mengandung :size item.',
    ],
    'starts_with' => 'Field :attribute harus dimulai dengan salah satu dari: :values.',
    'string' => 'Field :attribute harus berupa string.',
    'timezone' => 'Field :attribute harus zona waktu yang valid.',
    'unique' => 'Field :attribute sudah ada.',
    'uploaded' => 'Field :attribute gagal diunggah.',
    'url' => 'Format field :attribute tidak valid.',
    'uuid' => 'Field :attribute harus UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "rule.attribute" to name the lines. This makes it quick to
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

    'attributes' => [],
];