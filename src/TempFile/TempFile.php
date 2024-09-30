<?php

    namespace TempFile;

    use Exception;
    use InvalidArgumentException;
    use RuntimeException;

    class TempFile
    {
        private const string RANDOM_CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        /**
         * An array of temporary files to be deleted on shutdown
         *
         * @var string[]
         */
        private static array $temporary_files = [];

        /**
         * Indicates whether the shutdown handler has been registered
         *
         * @var bool
         */
        private static bool $shutdown_handler_registered = false;

        /**
         * @var string
         */
        private string $filename;

        /**
         * @var string
         */
        private string $filepath;

        /**
         * Create a new temporary file with optional options:
         *  - extension: The extension of the file
         *  - filename: The filename of the file
         *  - prefix: The prefix of the file name
         *  - suffix: The suffix of the file name
         *
         * @param array|null $options
         * @throws Exception
         */
        public function __construct(?array $options=[])
        {
            /** @var string|int $value */
            foreach($options as $option => $value)
            {
                if(!is_string($value) && !is_int($value))
                {
                    throw new InvalidArgumentException(sprintf('The value for option %s must be a string or int, got %s', $option, gettype($value)));
                }

                if(!in_array($option, Options::All))
                {
                    throw new InvalidArgumentException(sprintf('The option %s is not valid', $option));
                }
            }

            if(!isset($options[Options::Extension]))
            {
                $options[Options::Extension] = 'tmp';
            }

            if(!isset($options[Options::RandomLength]))
            {
                $options[Options::RandomLength] = 8;
            }

            if(isset($options[Options::Filename]))
            {
                $this->filename = $options[Options::Filename];
            }
            else
            {
                $this->filename = self::randomString((int)$options[Options::RandomLength]);
            }

            if(isset($options[Options::Prefix]))
            {
                $this->filename = $options[Options::Prefix] . $this->filename;
            }

            if(isset($options[Options::Suffix]))
            {
                $this->filename = $this->filename . $options[Options::Suffix];
            }

            $this->filename .= '.' . $options[Options::Extension];
            $replaced = preg_replace('/[^a-zA-Z0-9.\-_]/', '', $this->filename);

            if($replaced === false)
            {
                throw new InvalidArgumentException('The filename contains invalid characters');
            }

            if(is_array($replaced))
            {
                $replaced = implode('', $replaced);
            }

            $this->filename = $replaced;

            if(isset($options[Options::Directory]))
            {
                if(!file_exists($options[Options::Directory]) || !is_dir($options[Options::Directory]))
                {
                    throw new InvalidArgumentException(sprintf('The directory %s does not exist or is not a a valid path', $options[Options::Directory]));
                }
                if(!is_writable($options[Options::Directory]))
                {
                    throw new InvalidArgumentException(sprintf('The directory %s is not writable', $options[Options::Directory]));
                }

                $this->filepath = $options[Options::Directory] . DIRECTORY_SEPARATOR . $this->filename;
            }
            else
            {
                $this->filepath = self::getTempDir() . DIRECTORY_SEPARATOR . $this->filename;
            }

            if(!file_exists($this->filepath))
            {
                touch($this->filepath);
            }

            if(!is_writable($this->filepath))
            {
                throw new Exception('Unable to create temporary file');
            }

            self::$temporary_files[] = $this->filepath;
            if(!self::$shutdown_handler_registered && function_exists('register_shutdown_function'))
            {
                register_shutdown_function([self::class, 'shutdownHandler']);
                self::$shutdown_handler_registered = true;
            }
        }

        /**
         * Generates a random string of a given length
         *
         * @param int $length
         * @return string
         */
        private static function randomString(int $length=8): string
        {
            $charactersLength = strlen(self::RANDOM_CHARACTERS);
            $randomString = '';

            for ($i = 0; $i < $length; $i++)
            {
                $randomString .= self::RANDOM_CHARACTERS[rand(0, $charactersLength - 1)];
            }

            return $randomString;
        }

        /**
         * Attempts to get the temporary directory
         *
         * @return string
         * @throws Exception
         */
        private  static function getTempDir(): string
        {
            if(function_exists('sys_get_temp_dir'))
            {
                return sys_get_temp_dir();
            }

            $paths = [
                DIRECTORY_SEPARATOR . 'tmp',
                DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'tmp'
            ];

            foreach($paths as $path)
            {
                if(is_dir($path) && is_writable($path))
                {
                    return $path;
                }
            }

            $local_tmp = getcwd() . DIRECTORY_SEPARATOR . 'temp';

            if(is_writeable(getcwd()))
            {
                if(!is_dir($local_tmp))
                {
                    mkdir($local_tmp);
                }

                return $local_tmp;
            }

            throw new RuntimeException('Unable to find a suitable temporary directory');
        }

        /**
         * Returns the name of the temporary file
         *
         * @return string
         */
        public function getFilename(): string
        {
            return $this->filename;
        }

        /**
         * Returns the absolute path to the temporary file
         *
         * @return string
         */
        public function getFilepath(): string
        {
            return $this->filepath;
        }

        /**
         * Returns the filepath of the temporary file
         *
         * @return string
         */
        public function __toString(): string
        {
            return $this->filepath;
        }

        /**
         * Handles the shutdown event by deleting all temporary files if the destructors have not been called
         *
         * @return void
         */
        private static function shutdownHandler(): void
        {
            if(count(self::$temporary_files) == 0)
            {
                return;
            }

            foreach(self::$temporary_files as $file)
            {
                if(file_exists($file) && is_writable($file))
                {
                    @unlink($file);
                    unset(self::$temporary_files[$file]);
                }
            }
        }

        /**
         * Deletes the temporary file
         */
        public function __destruct()
        {
            if(file_exists($this->filepath))
            {
                if(!is_writeable($this->filepath))
                {
                    trigger_error(sprintf('Unable to delete temporary file %s', $this->filepath), E_USER_WARNING);
                    return;
                }

                unlink($this->filepath);
                unset(self::$temporary_files[$this->filepath]);
            }
        }
    }