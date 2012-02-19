<?php
/**
 * Basic test cases for framework
 *
 * @version $Revision$
 * @license GPLv3
 */

namespace Vcs\Wrapper\Archive;

use \Vcs\TestCase;

use \Vcs\Cache;
use \Vcs\Wrapper\Archive\File;
use \Vcs\Wrapper\Archive\Checkout\Zip;

/**
 * Tests for the SQLite cache meta data handler
 */
class ArchiveFileTest extends TestCase
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
        Cache::initialize( $this->createTempDir() );
    }

    public function testGetFileContents()
    {
        $repository = new Zip( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "Some test contents\n",
            $file->getContents()
        );
    }

    public function testGetFileMimeType()
    {
        $repository = new Zip( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            "application/octet-stream",
            $file->getMimeType()
        );
    }

    public function testGetLocalFilePath()
    {
        $repository = new Zip( $this->tempDir );
        $repository->initialize( $this->getRepositoryArchive( 'archive' ) );
        $file = new File( $this->tempDir, '/dir1/file' );

        $this->assertEquals(
            $this->tempDir . '/dir1/file',
            $file->getLocalPath()
        );
    }
}

