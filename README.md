Cabinet is a filestore abstraction written in PHP. Filestores map string keys to
files.

At the core of Cabinet is the `FileStore` interface, which defines most of the
operations you'd find in a normal filesystem (read, write, delete, etc.). By
programming to this interface, your application code remains unaware of the
underlying filestore implementation.

Abstracting the filestore lets you swap one implementation for another without
requiring changes to your application code. For example, you might use S3 in
production, a local filesystem in dev, and an array-backed implementation in
your unit tests.


## Filestore implementations

Cabinet comes with a number of filestore implementations:

 * `FilesystemFileStore` stores files in your local filesystem.
 * `ArrayFileStore` stores files in a PHP array.
 * `NullFileStore` is a black hole like `/dev/null`.

There are two additional wrapper implementations:

 * `ReadOnlyFileStore` delegates read and write operations to two separate
    filestores.
 * `MountPointFileStore` provides a way to compose multiple filestores into a
   virtual filesystem.

To provide a custom implementation, write a class that implements
`Cabinet\FileStore`.


## Example usage

You'd typically defer construction of a filestore to a factory constructor:

    class FileStoreFactory
    {
        public static function create() {
            // This might return an implementation based on your app config
            return new \Cabinet\FilesystemFileStore('/tmp/myfiles');
        }
    }

Then your application code uses the filestore like so:

    $store = FileStoreFactory::create();
    $file = $store->newFile('/foo/bar');
    $store->setFileContents($file, 'some data');
    $store->close($file);

This writes the given data to `/tmp/myfiles/foo/bar`.
