<?php

    namespace TempFile;

    abstract class Options
    {
        const Extension = 'extension';
        const Filename = 'filename';
        const Prefix = 'prefix';
        const Suffix = 'suffix';
        const RandomLength = 'random_length';
        const Directory = 'directory';

        Const All = [
            self::Extension,
            self::Filename,
            self::Prefix,
            self::Suffix,
            self::RandomLength,
            self::Directory,
        ];
    }
