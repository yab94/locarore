<?php

declare(strict_types=1);

namespace Rore\Infrastructure\File;

use RRB\File\FileManager;
use Rore\Application\Catalog\Port\FileManagerInterface;

final class FileManagerAdapter extends FileManager implements FileManagerInterface {}
