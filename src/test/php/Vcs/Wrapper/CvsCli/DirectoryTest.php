<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\CvsCli;

use \Vcs\TestCase;
use \RecursiveIteratorIterator;

/**
 * Tests for the CVS Cli wrapper
 */
class DirectoryTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if ( false === $this->hasBinary( 'cvs' ) )
        {
            $this->markTestSkipped( 'CVS binary not found.' );
        }

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \vcsCache::initialize( $this->createTempDir() );
    }

    public function testIterateRootDirContents()
    {
        $repository = new \vcsCvsCliCheckout( $this->tempDir );
        $repository->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $dir = new \vcsCvsCliDirectory( $this->tempDir, '/' );

        $files = array();
        foreach ( $dir as $file )
        {
            // Stupid, but cvs also checks out the not versions .svn folders
            if ( strpos( (string) $file, '.svn' ) === false )
            {
                $files[] = (string) $file;
            }
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/dir1/',
                '/file'
            ),
            $files
        );
    }

    public function testRecursiveIterator()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $dir      = new \vcsCvsCliDirectory( $this->tempDir, '/' );
        $iterator = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::SELF_FIRST );

        $files = array();
        foreach ( $iterator as $file )
        {
            // Stupid, but cvs also checks out the not versions .svn folders
            if ( strpos( (string) $file, '.svn' ) === false )
            {
                $files[] = (string) $file;
            }
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/dir1/',
                '/dir1/file',
                '/dir1/file1',
                '/file'
            ),
            $files
        );
    }

    public function testIterateSubDirContents()
    {
        $checkout = new \vcsCvsCliCheckout( $this->tempDir );
        $checkout->initialize( $this->extractRepository( 'cvs' ) . '#cvs' );

        $dir = new \vcsCvsCliDirectory( $this->tempDir, '/dir1/' );

        $files = array();
        foreach ( $dir as $file )
        {
            // Stupid, but cvs also checks out the not versions .svn folders
            if ( strpos( (string) $file, '.svn' ) === false )
            {
                $files[] = (string) $file;
            }
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/dir1/file',
                '/dir1/file1',
            ),
            $files
        );
    }
}
