<?php
/**
 * PHP VCS wrapper SVN Cli file wrapper
 *
 * This file is part of vcs-wrapper.
 *
 * vcs-wrapper is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * vcs-wrapper is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with vcs-wrapper; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace Vcs\Wrapper\SvnCli;

use \Vcs\Blame;
use \Vcs\Blameable;
use \Vcs\Cache;
use \Vcs\Fetchable;
use \Vcs\NoSuchVersionException;
use \SystemProcess\Argument\PathArgument;

/**
 * File implementation vor SVN Cli wrapper
 *
 * @version $Revision$
 */
class File extends Resource implements \Vcs\File, Blameable, Fetchable
{
    /**
     * Get file contents
     * 
     * Get the contents of the current file.
     * 
     * @return string
     */
    public function getContents()
    {
        return file_get_contents( $this->root . $this->path );
    }

    /**
     * Get mime type
     * 
     * Get the mime type of the current file. If this information is not
     * available, just return 'application/octet-stream'.
     * 
     * @return string
     */
    public function getMimeType()
    {
        $mimeType = $this->getResourceProperty( 'mime-type' );

        if ( !empty( $mimeType ) )
        {
            return $mimeType;
        }

        // If not set, fall back to application/octet-stream
        return 'application/octet-stream';
    }

    /**
     * Get blame information for resource
     *
     * The method should return author and revision information for each line,
     * describing who when last changed the current resource. The returned
     * array should look like:
        
     * <code>
     *  array(
     *      T_LINE_NUMBER => array(
     *          'author'  => T_STRING,
     *          'version' => T_STRING,
     *      ),
     *      ...
     *  );
     * </code>
     *
     * If some file in the repository has no blame information associated, like
     * binary files, the method should return false.
     *
     * Optionally a version may be specified which defines a later version of
     * the resource for which the blame information should be returned.
     *
     * @param mixed $version
     * @return mixed
     */
    public function blame( $version = null )
    {
        $version = ( $version === null ) ? $this->getVersionString() : $version;

        if ( !in_array( $version, $this->getVersions(), true ) )
        {
            throw new NoSuchVersionException( $this->path, $version );
        }

        if ( ( $blame = Cache::get( $this->path, $version, 'blame' ) ) === false )
        {
            // Refetch the basic blamermation, and cache it.
            $process = new Process( 'svn', $this->username, $this->password );
            $process->argument( '--xml' );

            // Execute command
            $process->argument( 'blame' )->argument( new PathArgument( $this->root . $this->path ) )->execute();
            $xml = simplexml_load_string( $process->stdoutOutput );

            // Check if blame information si available. Is absent fro binary
            // files.
            if ( !$xml->target )
            {
                return false;
            }

            $blame = array();
            $contents = preg_split( '(\r\n|\r|\n)', $this->getVersionedContent( $version ) );

            $offset = 0;
            foreach ( $xml->target[0]->entry as $entry )
            {
                $blame[] = new Blame(
                    (string) $contents[$offset++],
                    (string) $entry->commit[0]['revision'],
                    (string) $entry->commit[0]->author,
                    strtotime( (string) $entry->commit[0]->date )
                );
            }

            Cache::cache( $this->path, $version, 'blame', $blame );
        }

        return $blame;
    }

    /**
     * Get content for version
     *
     * Get the contents of the current resource in the specified version.
     *
     * @param string $version 
     * @return string
     */
    public function getVersionedContent( $version )
    {
        if ( !in_array( $version, $this->getVersions(), true ) )
        {
            throw new NoSuchVersionException( $this->path, $version );
        }

        if ( ( $content = Cache::get( $this->path, $version, 'content' ) ) === false )
        {
            // Refetch the basic content information, and cache it.
            $process = new Process( 'svn', $this->username, $this->password );
            $process->argument( '-r' . $version );

            // Execute command
            $process->argument( 'cat' )->argument( new PathArgument( $this->root . $this->path ) )->execute();
            Cache::cache( $this->path, $version, 'content', $content = $process->stdoutOutput );
        }

        return $content;
    }
}

