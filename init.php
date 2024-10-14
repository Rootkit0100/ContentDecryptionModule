<?php

global $loader;
global $plugins;

$loader->addPsr4('ContentDecryptionPlugin\\', __DIR__ . '/src/');

$plugins[] = [
    ContentDecryptionPlugin\Plugin::NAME,
    '\ContentDecryptionPlugin\ContentDecryptionPluginServiceProvider',
];
