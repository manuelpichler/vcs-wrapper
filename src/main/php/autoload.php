<?php
/**
 * Autoload file
 *
 * This file is part of vcs_wrapper
 *
 * vcs_wrapper is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * vcs_wrapper is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with vcs_wrapper; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/*
 * This array is autogenerated and topoligically sorted. Do not change anything
 * in here, but just run the following script:
 *
 * # scripts/gen_autoload_files.php
 */

spl_autoload_register( function( $class ) {
    static $paths = array(
        'pbsSystemProcessInvalidCustomDescriptorException' => 'external/exceptions/system_process/invalidCustomFileDescriptor.php',
        'pbsSystemProcessNonZeroExitCodeException'         => 'external/exceptions/system_process/nonZeroExitCode.php',
        'pbsSystemProcessNotRunningException'              => 'external/exceptions/system_process/notRunning.php',
        'pbsSystemProcessRecursivePipeException'           => 'external/exceptions/system_process/recursivePipe.php',
        'pbsArgument'                                      => 'external/system_process/argument.php',
        'pbsEscapedArgument'                               => 'external/system_process/argument/escaped.php',
        'pbsPathArgument'                                  => 'external/system_process/argument/path.php',
        'pbsUnescapedArgument'                             => 'external/system_process/argument/unescaped.php',
        'pbsSystemProcess'                                 => 'external/system_process/systemProcess.php',
    );

    if ( isset( $paths[$class] ) )
    {
        include __DIR__ . DIRECTORY_SEPARATOR . $paths[$class];
    }
    else if ( 0 === strpos( $class, 'Vcs\\' ) )
    {
        include __DIR__ . '/' . strtr( $class, '\\', '/' ) . '.php';
    }
} );
