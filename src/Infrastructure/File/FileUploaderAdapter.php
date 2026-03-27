<?php

declare(strict_types=1);

namespace Rore\Infrastructure\File;

use RRB\File\FileUploader;
use Rore\Application\Catalog\Port\FileUploaderInterface;

final class FileUploaderAdapter extends FileUploader implements FileUploaderInterface {}
