<?php

return [
    'cards' => [
        [
            'key'   => 'dashboard',
            'label' => 'Dashboard',
            'route' => 'dashboard',
            'roles' => ['admin','pengurus','degur','santri','wali'],
        ],

        [
            'key'   => 'users',
            'label' => 'Manajemen User',
            'route' => 'admin.users.index',
            'roles' => ['admin'],
        ],

        [
            'key'   => 'profile',
            'label' => 'Manajemen Profil',
            'route' => 'profile.edit',
            'roles' => ['admin','pengurus','degur','santri','wali'],
        ],

        [
            'key'   => 'wali_anak',
            'label' => 'Data Anak Saya',
            'route' => 'wali.anak',
            'roles' => ['wali'],
        ],

        [
            'key'   => 'santri_home',
            'label' => 'Beranda Santri',
            'route' => 'santri.home',
            'roles' => ['santri'],
        ],

        [
            'key'   => 'log_all',
            'label' => 'Log Keluar Masuk (All)',
            'route' => null, // belum tersedia
            'roles' => ['admin','pengurus','degur'],
        ],

        [
            'key'   => 'log',
            'label' => 'Log Keluar Masuk',
            'route' => 'santri.data.log',
            'roles' => ['santri'],
        ],

        [
            'key'   => 'presensi_all',
            'label' => 'Presensi (All)',
            'route' => null, // belum tersedia
            'roles' => ['admin','pengurus','degur'],
        ],

        [
            'key'   => 'presensi',
            'label' => 'Presensi',
            'route' => 'santri.data.presensi',
            'roles' => ['santri'],
        ],

        [
            'key'   => 'progres_all',
            'label' => 'Progres Keilmuan (All)',
            'route' => null, // belum tersedia
            'roles' => ['admin','pengurus','degur'],
        ],

        [
            'key'   => 'progres',
            'label' => 'Progres Keilmuan',
            'route' => 'santri.data.progres',
            'roles' => ['santri'],
        ],
    ],
];

