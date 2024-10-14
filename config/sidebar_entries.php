<?php
$panelsPermissions = [
    'indexContentDecryption',
];

return [
    [
        'entry' => [
            "Content Decryption" => array(
                "can" => $panelsPermissions,
                "icon" => "fab fa-mendeley",
                "subcategories" => array(
                    1 => array(
                        "title" => "Mpd Streams",
                        "can" => $panelsPermissions,
                        "items" => array(
                            0 => array(
                                "title" => "Add Mpd Stream",
                                "can" => "indexContentDecryption",
                                "route" => "content_decryption.create",
                            ),
                            1 => array(
                                "title" => "Manage Mpd Streams",
                                "can" => "indexContentDecryption",
                                "matching_routes" => ["content_decryption.index", "content_decryption.edit"],
                                "route" => "content_decryption.index",
                            ),

                        ),
                    ),
                )
            ),
        ],
    ],
];
