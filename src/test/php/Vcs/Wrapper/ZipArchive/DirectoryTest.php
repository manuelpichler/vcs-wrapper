<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\ZipArchive;

use \Vcs\TestCase;
use \RecursiveIteratorIterator;

/**
 * Tests for the SQLite cache meta data handler
 */
class DirectoryTest extends TestCase
{
    protected function setUp()
    {
        if ( !class_exists( 'ZipArchive' ) )
        {
            $this->markTestSkipped( 'Compile PHP with --enable-zip to get support for zip archive handling.' );
        }

        parent::setUp();

        // Create a cache, required for all VCS wrappers to store metadata
        // information
        \vcsCache::initialize( $this->createTempDir() );
    }

    public function testIterateRootDirContents()
    {
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

        $dir = new \vcsArchiveDirectory( $this->tempDir, '/' );

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
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

        $dir      = new \vcsArchiveDirectory( $this->tempDir, '/' );
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
                '/file'
            ),
            $files
        );
    }

    public function testIterateSubDirContents()
    {
        $repository = new \vcsZipArchiveCheckout( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );

        $dir = new \vcsArchiveDirectory( $this->tempDir, '/dir1/' );

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

