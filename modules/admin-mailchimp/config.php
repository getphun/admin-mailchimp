<?php
/**
 * admin-mailchimp config file
 * @package admin-mailchimp
 * @version 0.0.1
 * @upgrade true
 */

return [
    '__name' => 'admin-mailchimp',
    '__version' => '0.0.1',
    '__git' => 'https://github.com/getphun/admin-mailchimp',
    '__files' => [
        'modules/admin-mailchimp' => [ 'install','remove','update' ],
        'theme/admin/partner/mailchimp' => [ 'install','remove','update' ],
    ],
    '__dependencies' => [
        'admin',
        'site-param'
    ],
    '_services' => [
        'mc' => 'AdminMailchimp\\Service\\Mailchimp'
    ],
    '_autoload' => [
        'classes' => [
            'DrewM\\MailChimp\\MailChimp'                   => 'modules/admin-mailchimp/library/MailChimp.php',
            'DrewM\\MailChimp\\Batch'                       => 'modules/admin-mailchimp/library/Batch.php',
            'AdminMailchimp\\Controller\\ChimpController'   => 'modules/admin-mailchimp/controller/ChimpController.php',
            'AdminMailchimp\\Service\\Mailchimp'            => 'modules/admin-mailchimp/service/Mailchimp.php'
        ],
        'files' => []
    ],
    
    '_routes' => [
        'admin' => [
            'adminMailchimp' => [
                'rule' => '/mailchimp',
                'handler' => 'AdminMailchimp\\Controller\\Chimp::index'
            ],
            'adminMailchimpImport' => [
                'rule' => '/mailchimp/import',
                'handler' => 'AdminMailchimp\\Controller\\Chimp::import'
            ],
            'adminMailchimpEdit' => [
                'rule' => '/mailchimp/:id',
                'handler' => 'AdminMailchimp\\Controller\\Chimp::edit'
            ],
            'adminMailchimpRemove' => [
                'rule' => '/mailchimp/:id/delete',
                'handler' => 'AdminMailchimp\\Controller\\Chimp::remove'
            ]
        ]
    ],
    
    
    'admin' => [
        'menu' => [
            'partner' => [
                'label'     => 'Partner',
                'icon'      => 'handshake-o',
                'order'     => 1000,
                'submenu'   => [
                    'mailchimp' => [
                        'label'     => 'Mailchimp',
                        'perms'     => 'read_mailchimp',
                        'target'    => 'adminMailchimp',
                        'order'     => 100
                    ]
                ]
            ]
        ]
    ],
    
    'form' => [
        'admin-mailchimp' => [
            'email'     => [
                'type'      => 'email',
                'label'     => 'Email',
                'rules'     => [
                    'email'     => true
                ]
            ],
            'first_name' => [
                'type'      => 'text',
                'label'     => 'First Name',
                'rules'     => []
            ],
            'last_name' => [
                'type'      => 'text',
                'label'     => 'Last Name',
                'rules'     => []
            ],
            'status'    => [
                'type'      => 'select',
                'label'     => 'Status',
                'options'   => [
                    'subscribed'    => 'Subscribed',
                    'unsubscribed'  => 'Unsubscribed',
                    'cleaned'       => 'Cleaned',
                    'pending'       => 'Pending',
                    'transactional' => 'Transactional'
                ],
                'rules'     => [
                    'required' => true
                ]
            ]
        ],
        
        'admin-mailchimp-index' => [
            'email'     => [
                'type'      => 'text',
                'label'     => 'Find Email',
                'nolabel'   => true,
                'rules'     => []
            ],
            'status'    => [
                'type'      => 'select',
                'label'     => 'Status',
                'nolabel'   => true,
                'options'   => [
                    'subscribed'    => 'Subscribed',
                    'unsubscribed'  => 'Unsubscribed',
                    'cleaned'       => 'Cleaned',
                    'pending'       => 'Pending',
                    'transactional' => 'Transactional'
                ],
                'rules'     => []
            ]
        ],
        
        'admin-mailchimp-import' => [
            'emails'    => [
                'type'      => 'textarea',
                'label'     => 'Emails',
                'rules'     => [
                    'required'  => true
                ]
            ]
        ]
    ]
];