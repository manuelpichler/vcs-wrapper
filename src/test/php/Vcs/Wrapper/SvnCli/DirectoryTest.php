<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\SvnCli;

use \Vcs\TestCase;
use \RecursiveIteratorIterator;

/**
 * Tests for the SQLite cache meta data handler
 */
class DirectoryTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        if ( false === $this->hasBinary( 'svn' ) )
        {
            $this->markTestSkipped( 'Svn binary not found.' );
        }

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \vcsCache::initialize( $this->createTempDir() );
    }

    public function testIterateRootDirContents()
    {
        $repository = new \vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $dir = new \vcsSvnCliDirectory( $this->tempDir, '/' );

        $files = array();
        foreach ( $dir as $file )
        {
            $files[] = (string) $file;
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/binary',
                '/dir1/',
                '/dir2/',
                '/file'
            ),
            $files
        );
    }

    public function testRecursiveIterator()
    {
        $repository = new \vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $dir      = new \vcsSvnCliDirectory( $this->tempDir, '/' );
        $iterator = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::SELF_FIRST );

        $files = array();
        foreach ( $iterator as $file )
        {
            $files[] = (string) $file;
        }
        sort( $files );

        $this->assertEquals(
            array(
                '/binary',
                '/dir1/',
                '/dir1/file',
                '/dir2/',
                '/file'
            ),
            $files
        );
    }

    public function testIterateSubDirContents()
    {
        $repository = new \vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );

        $dir = new \vcsSvnCliDirectory( $this->tempDir, '/dir1/' );

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

    public function testGetDirectoryDiff()
    {
        $repository = new \vcsSvnCliCheckout( $this->tempDir );
        $repository->initialize( 'file://' . $this->extractRepository( 'svn' ) );
        $dir = new \vcsSvnCliDirectory( $this->tempDir, '/dir1/' );

        $diff = $dir->getDiff( 2 );

        $this->assertEquals(
            '/dir1/file',
            $diff[0]->from
        );
        $this->assertEquals(
            '/dir1/file',
            $diff[0]->to
        );
        $this->assertEquals(
            array(
                new \vcsDiffChunk(
                    0, 1, 1, 1,
                    array(
                        new \vcsDiffLine( 1, 'Some test contents' ),
                    )
                ),
            ),
            $diff[0]->chunks
        );
    }
}

