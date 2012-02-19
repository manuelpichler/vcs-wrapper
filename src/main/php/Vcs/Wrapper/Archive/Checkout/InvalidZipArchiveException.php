<?php
/**
 * PHP VCS wrapper ZIP archive based repository wrapper
 *
 * This file is part of \vcs-wrapper.
 *
 * \vcs-wrapper is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * \vcs-wrapper is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with \vcs-wrapper; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package VCSWrapper
 * @subpackage ArchiveWrapper
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace Vcs\Wrapper\Archive\Checkout;

use \ZipArchive;
use \RuntimeException;

/**
 * Exception thrown when a ZIP archive could not be opened by the PHP class
 * ZipArchive, which just returns some failue code in this case.
 *
 * @version $Revision$
 */
class InvalidZipArchiveException extends RuntimeException
{
    /**
     * Failure messages for the error codes.
     *
     * @var array
     */
    protected $messages = array(
        ZipArchive::ER_OK          => 'No error.',
        ZipArchive::ER_MULTIDISK   => 'Multi-disk zip archives not supported.',
        ZipArchive::ER_RENAME      => 'Renaming temporary file failed.',
        ZipArchive::ER_CLOSE       => 'Closing zip archive failed',
        ZipArchive::ER_SEEK        => 'Seek error',
        ZipArchive::ER_READ        => 'Read error',
        ZipArchive::ER_WRITE       => 'Write error',
        ZipArchive::ER_CRC         => 'CRC error',
        ZipArchive::ER_ZIPCLOSED   => 'Containing zip archive was closed',
        ZipArchive::ER_NOENT       => 'No such file.',
        ZipArchive::ER_EXISTS      => 'File already exists',
        ZipArchive::ER_OPEN        => 'Can\'t open file',
        ZipArchive::ER_TMPOPEN     => 'Failure to create temporary file.',
        ZipArchive::ER_ZLIB        => 'Zlib error',
        ZipArchive::ER_MEMORY      => 'Memory allocation failure',
        ZipArchive::ER_CHANGED     => 'Entry has been changed',
        ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported.',
        ZipArchive::ER_EOF         => 'Premature EOF',
        ZipArchive::ER_INVAL       => 'Invalid argument',
        ZipArchive::ER_NOZIP       => 'Not a zip archive',
        ZipArchive::ER_INTERNAL    => 'Internal error',
        ZipArchive::ER_INCONS      => 'Zip archive inconsistent',
        ZipArchive::ER_REMOVE      => 'Can\'t remove file',
        ZipArchive::ER_DELETED     => 'Entry has been deleted',
    );

    /**
     * Construct exception
     *
     * @param string $file
     * @param int $code
     */
    public function __construct( $file, $code )
    {
        parent::__construct( "Error extracting $file: " . $this->messages[$code] );
    }
}
