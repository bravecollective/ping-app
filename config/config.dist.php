<?php

return [
    // Slim
    'displayErrorDetails' => false,
    'determineRouteBeforeAppMiddleware' => true,

    // SSO CONFIGURATION
    'SSO_CLIENT_ID' => '',
    'SSO_CLIENT_SECRET' => '',
    'SSO_REDIRECTURI' => '',
    'SSO_URL_AUTHORIZE' => 'https://login.eveonline.com/oauth/authorize',
    'SSO_URL_ACCESSTOKEN' => 'https://login.eveonline.com/oauth/token',
    'SSO_URL_RESOURCEOWNERDETAILS' => 'https://esi.evetech.net/verify/',
    'SSO_SCOPES' => '',

    // App
    'brave.serviceName' => 'Brave Pings',

    // SLACKBOT access token
    'SLACK_TOKEN' => '',

    // NEUCORE
    'CORE_URL' => 'https://account.bravecollective.com/api',
    'CORE_APP_ID' => '',
    'CORE_APP_TOKEN' => '',

    // DB
    'DB_URL' => '',

    'pingMapping' => [
        'member' => ['Social', 'Diplos'],
        'admin' => ['Announcement']
    ],

    'channelMapping' => [
        'Social' => 'pings-social',
        'Diplos' => 'diplo',
        'Announcement' => 'announcements'
    ],

    'templates' => [
        '__default' => '',
    ],
];
