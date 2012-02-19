<?php
/**
 * PHP VCS wrapper abstract file base class
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

namespace Vcs;

use \LogicException;

/**
 * Exception thrown when a file or directory is requested from a
 * repository, which is not part of the repository.
 *
 * @version $Revision$
 */
class FileNotFoundException extends LogicException
{
    /**
     * Construct exception
     *
     * @param string $file
     */
    public function __construct( $file )
    {
        parent::__construct( "Could not locate '$file' inside the repository." );
    }
}
