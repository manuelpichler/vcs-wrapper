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
 * Exception thrown when a version is requested from a repository, which does
 * not exist.
 *
 * @version $Revision$
 */
class NoSuchVersionException extends LogicException
{
    /**
     * Construct exception
     *
     * @param string $path
     * @param string $version
     */
    public function __construct( $path, $version )
    {
        parent::__construct( "There is no version '$version' of resource '$path'." );
    }
}
