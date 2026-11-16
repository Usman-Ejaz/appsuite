<?php

return [
    'name' => 'Notification',

    /*
     * The queue trigger processing and notification sending run on. Point a worker at it with
     * `queue:work --queue=<name>` when it is changed from the default queue.
     */
    'queue' => env('NOTIFICATION_QUEUE', 'default'),
];
