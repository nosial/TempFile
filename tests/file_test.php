<?php

    require 'ncc';
    import('net.nosial.tempfile');

    $temp = new \TempFile\TempFile([
        \TempFile\Options::Extension => 'txt',
        \TempFile\Options::Filename => 'test',
    ]);
    print(sprintf('Tempfile: %s', $temp->getFilepath()) . PHP_EOL);

    file_put_contents($temp, 'Hello, world!');
    print(sprintf('Filesize: %s', filesize($temp->getFilepath())) . PHP_EOL);

    sleep(10);
    print('Exiting...' . PHP_EOL);
    exit(0);