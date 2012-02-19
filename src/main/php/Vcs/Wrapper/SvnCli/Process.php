<?php
/**
 * PHP VCS wrapper SVN system process class
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

use \SystemProcess\SystemProcess;

/**
 * Svn executable wrapper for system process class
 *
 * @version $Revision$
 */
class Process extends SystemProcess
{
    /**
     * Class constructor taking the executable
     * 
     * @param string $executable 
     * @param string $user 
     * @param string $password
     */
    public function __construct( $executable = 'svn', $user = null, $password = null )
    {
        parent::__construct( $executable );

        $this->nonZeroExitCodeException = true;
        $this->argument( '--non-interactive' );

        if ( $user !== null )
        {
            $this->argument( '--username' )->argument( $user );

            if ( $password !== null )
            {
                $this->argument( '--password' )->argument( $password );
            }
        }
    }
}

