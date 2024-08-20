<?php

return [
    [
        'code'         => 'dashboard',
        'name'         => "Dashboard",
        'url'          => "/",
        'label'         => "dashboard",
        'icon'         => "dashboard",
        'route_name' => "dashboard",
        'module'     => [
            'code' => 'dashboard',
            'task' => [
                [
                    'code' => 'add',
                    'name' => 'Add'
                ]
            ]
        ],
        'child'      => [],
    ],
    [
        'code'         => 'users',
        'name'         => "Users",
        'url'          => "/users",
        'label'         => "users",
        'icon'         => "users",
        'route_name' => "users",
        'module'     => [
            'code' => 'users',
            'task' => [
                [
                    'code' => 'create',
                    'name' => 'Create'
                ],
                [
                    'code' => 'read',
                    'name' => 'Read'
                ],
                [
                    'code' => 'update',
                    'name' => 'Update'
                ],
                [
                    'code' => 'delete',
                    'name' => 'Delete'
                ],
                [
                    'code' => 'restore',
                    'name' => 'Restore'
                ]
            ]
        ],
        'child'      => [],
    ],
    [
        'code'         => 'roles',
        'name'         => "Roles & Permissions",
        'url'          => "/roles",
        'label'         => "roles",
        'icon'         => "roles",
        'route_name' => "roles",
        'module'     => [
            'code' => 'roles',
            'task' => [
                [
                    'code' => 'create',
                    'name' => 'Create'
                ],
                [
                    'code' => 'read',
                    'name' => 'Read'
                ],
                [
                    'code' => 'update',
                    'name' => 'Update'
                ],
                [
                    'code' => 'delete',
                    'name' => 'Delete'
                ],
                [
                    'code' => 'restore',
                    'name' => 'Restore'
                ]
            ]
        ],
        'child'      => [],
    ]
    //example
    // [
    //     'code'         => 'pencatatan',
    //     'name'         => "Pencatatan",
    //     'label'        => "pencatatan",
    //     'url'          => "pencatatan",
    //     'icon'         => "cash-register",
    //     'route_name' => "",
    //     'module'      => [],
    //     'child'      => [
    //         [
    //             'code'         => "pencatatan-pemasukan",
    //             'name'         => "Daftar Pencatatan Pemasukan",
    //             'label'         => "pencatatanPemasukan",
    //             'icon'         => "",
    //             'url'           => 'pencatatan-pemasukan',
    //             'route_name' => "cmsPencatatanPemasukan",
    //             'module'      => [
    //                 'code' => 'pencatatan-pemasukan',
    //                 'task' => [
    //                     [
    //                         'code' => 'add-edit',
    //                         'name' => 'Add Edit'
    //                     ],
    //                     [
    //                         'code' => 'delete',
    //                         'name' => 'Delete'
    //                     ],
    //                     [
    //                         'code' => 'print',
    //                         'name' => 'Print'
    //                     ],
    //                     [
    //                         'code' => 'share-wa',
    //                         'name' => 'Share Whatsapp'
    //                     ]
    //                 ]
    //             ]
    //         ],
    //     ]
    // ],
];
