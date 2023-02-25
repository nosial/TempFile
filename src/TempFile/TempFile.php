<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace TempFile;

    use Exception;
    use ncc\Runtime;

    class TempFile
    {
        /**
         * An array of temporary files to be deleted on shutdown
         *
         * @var string[]
         */
        private static $temporary_files = [];

        /**
         * Indicates whether the shutdown handler has been registered
         *
         * @var bool
         */
        private static $shutdown_handler_registered = false;

        /**
         * The extension of the temporary file
         *
         * @var string
         */
        private $extension;

        /**
         * @var string
         */
        private $filename;

        /**
         * @var string
         */
        private $filepath;

        /**
         * Create a new temporary file with a given extension (default: tmp)
         *
         * @param string|null $extension
         * @throws Exception
         */
        public function __construct(?string $extension=null)
        {
            if($extension === null)
            {
                $extension = 'tmp';
            }

            $this->extension = ltrim($extension, '.');
            $this->filename = $this->randomString() . '.' . $this->extension;
            $this->filepath = $this->getTempDir() . DIRECTORY_SEPARATOR . $this->filename;

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
         * @return string
         */
        private function randomString(): string
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < 32; $i++)
            {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        /**
         * Attempts to get the temporary directory
         *
         * @return string
         * @throws Exception
         */
        private function getTempDir(): string
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

            try
            {
               return Runtime::getDataPath('net.nosial.tempfile');
            }
            catch(Exception $e)
            {
                unset($e);
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

            throw new Exception('Unable to find a suitable temporary directory');
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