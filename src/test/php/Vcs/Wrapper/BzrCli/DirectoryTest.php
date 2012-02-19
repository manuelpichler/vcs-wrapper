<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\BzrCli;

use \Vcs\TestCase;
use \RecursiveIteratorIterator;

/**
 * @group bazaar
 * Tests for the SQLite cache meta data handler
 */
class DirectoryTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if ( false === $this->hasBinary( 'bzr' ) )
        {
            $this->markTestSkipped( 'Bazaar binary not found.' );
        }

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \vcsCache::initialize( $this->createTempDir() );
    }

    public function testIterateRootDirContents()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );

        $dir = new Directory( $this->tempDir, '/' );

        $files = array();
        foreach ( $dir as $file )
        {
            $files[] = (string) $file;
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/dir1/',
                '/dir2/',
                '/file'
            ),
            $files
        );
    }

    public function testRecursiveIterator()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );

        $dir      = new Directory( $this->tempDir, '/' );
        $iterator = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::SELF_FIRST );

        $files = array();
        foreach ( $iterator as $file )
        {
            $files[] = (string) $file;
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/dir1/',
                '/dir1/file',
                '/dir2/',
                '/dir2/file',
                '/file'
            ),
            $files
        );
    }

    public function testIterateSubDirContents()
    {
        $repository = new Checkout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'bzr' ) );

        $dir = new Directory( $this->tempDir, '/dir1/' );

        $files = array();
        foreach ( $dir as $file )
        {
            $files[] = (string) $file;
        }

        $this->assertEquals(
            array(
                '/dir1/file'
            ),
            $files
        );
    }
}

