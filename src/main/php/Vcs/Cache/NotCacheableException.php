<?php
/**
 * PHP VCS wrapper base metadata cache metadata handler
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

namespace Vcs\Cache;

/**
 * Exception thrown when a value is passed to the cache, which is not
 * cacheable.
 *
 * @version $Revision$
 */
class NotCacheableException extends \vcsException
{
    /**
     * Construct exception
     *
     * @param mixed $value
     */
    public function __construct( $value )
    {
        parent::__construct( 'Value of type ' . gettype( $value ) . ' cannot be cached. Only arrays, scalar values and objects implementing Cacheable are allowed.' );
    }
}
