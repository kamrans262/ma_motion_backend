<?php

return [
    // Hostinger must provide ffprobe or set FFPROBE_BINARY to its absolute path.
    // Video uploads fail closed when duration cannot be verified.
    'ffprobe_binary' => env('FFPROBE_BINARY', 'ffprobe'),
];
