<?php

return [
    // SSO CONFIGURATION
    'SSO_CLIENT_ID' => '',
    'SSO_CLIENT_SECRET' => '',
    'SSO_REDIRECTURI' => '',
    'SSO_URL_AUTHORIZE' => 'https://login.eveonline.com/v2/oauth/authorize',
    'SSO_URL_ACCESSTOKEN' => 'https://login.eveonline.com/v2/oauth/token',
    'SSO_URL_JWKS' => 'https://login.eveonline.com/oauth/jwks',
    'SSO_SCOPES' => '',

    // App
    'brave.serviceName' => 'Brave Pings',

    // SLACKBOT access token - see https://your-name.slack.com/apps/A0F81R8ET-slackbot
    'SLACKBOT_URL' => 'https://your-name.slack.com/services/hooks/slackbot?token=ABC123',

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
