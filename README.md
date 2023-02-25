# TempFile

TempFile is a very simple library used for creating temporary files without having to write code to delete them
once you're done with them.

## Installation

The library can be installed using ncc:

```bash
ncc install -p "nosial/libs.tempfile=latest@n64"
```

or by adding the following to your project.json file under the `build.dependencies` section:

```json
{
  "name": "net.nosial.tempfile",
  "version": "latest",
  "source_type": "remote",
  "source": "nosial/libs.tempfile=latest@n64"
}
```

If you don't have the n64 source configured you can add it by running the following command:

```bash
ncc source add --name n64 --type gitlab --host git.n64.cc
```

## Compiling from source

The library can be compiled from source using ncc:

```bash
ncc build --config release
```

or by running the following command:

```bash
make release
```

## Usage

Just create a class object, optionally specifying a file extension to use (without the dot). And that's all, an
Exception will be thrown if the file could not be created.

```php
require_once('ncc');
import('net.nosial.tempfile');

$file1 = new TempFile();
$file2 = new TempFile('txt');
```

You can obtain the file path by using the `getFilepath()` method or by using the object as a string.

```php
echo $file1->getFilepath() . PHP_EOL;
file_put_contents($file2, 'Hello World!');
```

Files are automatically deleted when the object is destroyed, if for some reason the __destruct() method was not
properly called, a shutdown function is automatically registered to delete all the temporary files that were
created.

## License

This library is licensed under the MIT license, see the LICENSE file
for more information.