<?php

return [
    'name' => 'Inbox',
    'description' => 'Social Media Inbox Management Module',
    'version' => '1.0.0',
    
    // Pagination settings
    'per_page' => 4,
    
    // Supported social networks
    'supported_networks' => [
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'pinterest',
        'google_business'
    ],
    
    // Inbox types
    'inbox_types' => [
        'Messenger',
        'Comment',
        'AdComment',
        'DirectMessage'
    ],
    
    // Filter options
    'filter_options' => [
        'item_filters' => [
            'Inbox' => 'All Items',
            'Completed' => 'Completed Items',
            'Pending' => 'Pending Items'
        ],
        'favourite_filters' => [
            'Favourite' => 'Favourite Items'
        ]
    ],
    
    // Social network icons
    'network_icons' => [
        'facebook' => 'fab fa-facebook-f',
        'instagram' => 'fab fa-instagram',
        'twitter' => 'fab fa-x-twitter',
        'linkedin' => 'fab fa-linkedin',
        'pinterest' => 'fab fa-pinterest',
        'google_business' => 'fab fa-googlemybusiness'
    ],
    
    // Social network colors
    'network_colors' => [
        'facebook' => '#0074fa',
        'instagram' => '#E4405F',
        'twitter' => '#000',
        'linkedin' => '#0077b5',
        'pinterest' => '#cd2029',
        'google_business' => '#4b88ef'
    ]
];
